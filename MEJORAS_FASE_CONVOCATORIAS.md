# Mejoras al Sistema de Fases de Convocatorias

## Resumen de Cambios

Se han implementado mejoras significativas al módulo de **JobPosting** para resolver el problema de `getCurrentPhase()` retornando `null` y agregar funcionalidades avanzadas de gestión automática de fases.

---

## 1. Problema Original

### Síntoma
La función `getCurrentPhase()` retornaba `null` porque buscaba fases con estado `IN_PROGRESS`, pero todas estaban en estado `PENDING`.

### Causa Raíz
- No existía actualización automática de estados de fases
- Las fases debían iniciarse manualmente
- No había sincronización entre fechas/horas actuales y estados de fases

---

## 2. Solución Implementada

### 2.1 Campo de Duración en Horas

**Archivo**: `Modules/JobPosting/database/migrations/2026_01_01_201535_add_duration_hours_to_process_phases_table.php`

Se agregó el campo `default_duration_hours` a la tabla `process_phases` para soportar fases con duración menor a un día.

```php
$table->integer('default_duration_hours')->nullable()
    ->comment('Duración por defecto en horas (para fases cortas)');
```

**Actualizado**: `ProcessPhase.php` - Agregado el campo en `$fillable` y `$casts`.

---

### 2.2 Eventos de Transición de Fases

Se crearon 3 eventos para notificar cambios de estado:

**Archivos creados**:
- `Modules/JobPosting/Events/PhaseStarted.php` - Cuando una fase inicia
- `Modules/JobPosting/Events/PhaseCompleted.php` - Cuando una fase se completa
- `Modules/JobPosting/Events/PhaseDelayed.php` - Cuando una fase se retrasa

**Actualizado**: `JobPostingSchedule.php` - Los métodos `start()` y `complete()` ahora disparan eventos automáticamente.

```php
public function start(): void
{
    $this->update([
        'status' => ScheduleStatusEnum::IN_PROGRESS,
        'actual_start_date' => now(),
    ]);

    event(new \Modules\JobPosting\Events\PhaseStarted($this));
}
```

---

### 2.3 Comando de Actualización Automática

**Archivo**: `app/Console/Commands/UpdateJobPostingPhasesCommand.php`

Comando que actualiza automáticamente los estados de las fases según la fecha/hora actual.

#### Funcionalidades:
- ✅ Inicia fases `PENDING` cuya `start_date + start_time` ya pasó
- ✅ Completa fases `IN_PROGRESS` cuya `end_date + end_time` ya pasó
- ✅ Marca como `DELAYED` fases que pasaron su fecha límite sin completarse
- ✅ Auto-inicia la siguiente fase cuando una se completa
- ✅ Soporte completo para fechas + horas (no solo días)
- ✅ Modo `--dry-run` para simular sin hacer cambios

#### Uso:

```bash
# Ejecutar manualmente
php artisan jobposting:update-phases

# Simular sin hacer cambios
php artisan jobposting:update-phases --dry-run
```

#### Salida del Comando:
```
Iniciando actualización de fases...
Procesando 1 convocatorias activas...
Convocatoria: CONV-2026-001 - PRIMERA CONVOCATORIA
  → INICIANDO: Registro Virtual de Postulantes (desde 2025-12-30 00:00)
  ...

Resumen de actualización:
+-------------------+----------+
| Acción            | Cantidad |
+-------------------+----------+
| Fases iniciadas   | 12       |
| Fases completadas | 0        |
| Fases retrasadas  | 0        |
+-------------------+----------+
```

---

### 2.4 Mejora de getCurrentPhase()

**Archivo**: `Modules/JobPosting/app/Entities/JobPosting.php`

La función `getCurrentPhase()` ahora tiene un **fallback inteligente**:

1. **Primero** busca una fase explícitamente marcada como `IN_PROGRESS`
2. **Si no encuentra**, calcula la fase actual basándose en:
   - Fecha y hora actual
   - Rangos `start_date + start_time` hasta `end_date + end_time`
   - Soporte para fases sin hora (solo fecha)

```php
public function getCurrentPhase(): ?JobPostingSchedule
{
    // 1. Buscar fase marcada como IN_PROGRESS
    $inProgress = $this->schedules()
        ->where('status', ScheduleStatusEnum::IN_PROGRESS)
        ->with('phase')
        ->first();

    if ($inProgress) {
        return $inProgress;
    }

    // 2. Fallback: calcular por fechas/horas
    $now = now();

    return $this->schedules()
        ->with('phase')
        ->get()
        ->first(function($schedule) use ($now) {
            $start = \Carbon\Carbon::parse($schedule->start_date);
            $end = \Carbon\Carbon::parse($schedule->end_date);

            // Agregar horas si existen
            if ($schedule->start_time) {
                $timeParts = explode(':', $schedule->start_time);
                $start->setTime((int)$timeParts[0], (int)($timeParts[1] ?? 0));
            }

            if ($schedule->end_time) {
                $timeParts = explode(':', $schedule->end_time);
                $end->setTime((int)$timeParts[0], (int)($timeParts[1] ?? 0));
            }

            return $now->gte($start) && $now->lte($end);
        });
}
```

---

### 2.5 Observer para Auto-Inicio

**Archivo**: `Modules/JobPosting/Observers/JobPostingObserver.php`

Observer que detecta cuando una convocatoria se publica y automáticamente inicia la primera fase si ya debería estar activa.

```php
public function updated(JobPosting $jobPosting): void
{
    if ($jobPosting->isDirty('status') && $jobPosting->status === JobPostingStatusEnum::PUBLICADA) {
        $this->autoStartFirstPhaseIfNeeded($jobPosting);
    }
}
```

**Registrado en**: `Modules/JobPosting/app/Providers/JobPostingServiceProvider.php`

---

### 2.6 Programación Automática (Scheduler)

**Archivo**: `routes/console.php`

El comando se ejecuta **automáticamente cada hora** mediante el scheduler de Laravel:

```php
Schedule::command('jobposting:update-phases')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        \Log::info('JobPosting phases updated successfully');
    })
    ->onFailure(function () {
        \Log::error('JobPosting phases update failed');
    });
```

#### Activar el Scheduler

Para que las tareas programadas se ejecuten, debes configurar un cron job en tu servidor:

**Linux/Mac**:
```bash
* * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

**Windows (Task Scheduler)**:
- Programa: `php.exe`
- Argumentos: `C:\ruta\al\proyecto\artisan schedule:run`
- Frecuencia: Cada minuto

**Desarrollo local**:
```bash
php artisan schedule:work
```

---

## 3. Casos de Uso

### 3.1 Fases con Duración de Horas

Puedes configurar fases cortas usando `default_duration_hours` en `ProcessPhase`:

```php
ProcessPhase::create([
    'code' => 'PHASE_EVALUATION',
    'name' => 'Evaluación Técnica',
    'default_duration_days' => 0,
    'default_duration_hours' => 2,  // 2 horas de duración
]);
```

Luego en el Schedule:
```php
JobPostingSchedule::create([
    'start_date' => '2026-01-15',
    'start_time' => '09:00:00',
    'end_date' => '2026-01-15',
    'end_time' => '11:00:00',  // 2 horas después
]);
```

---

### 3.2 Escuchando Eventos de Fases

Crea listeners para reaccionar a eventos de fases:

```php
// app/Listeners/NotifyPhaseStarted.php
class NotifyPhaseStarted
{
    public function handle(PhaseStarted $event)
    {
        $schedule = $event->schedule;
        $posting = $schedule->jobPosting;

        // Enviar notificación
        Notification::send(
            User::role('admin')->get(),
            new PhaseHasStartedNotification($posting, $schedule)
        );
    }
}
```

Registrar en `EventServiceProvider`:
```php
protected $listen = [
    \Modules\JobPosting\Events\PhaseStarted::class => [
        \App\Listeners\NotifyPhaseStarted::class,
    ],
    \Modules\JobPosting\Events\PhaseCompleted::class => [
        \App\Listeners\NotifyPhaseCompleted::class,
    ],
    \Modules\JobPosting\Events\PhaseDelayed::class => [
        \App\Listeners\AlertPhaseDelayed::class,
    ],
];
```

---

## 4. Testing

### Verificar Estado Actual

```bash
php artisan tinker
>>> $posting = \Modules\JobPosting\Entities\JobPosting::first();
>>> $current = $posting->getCurrentPhase();
>>> $current->phase->name;
=> "Registro Virtual de Postulantes"
>>> $current->status->value;
=> "IN_PROGRESS"
```

### Simular Actualización

```bash
php artisan jobposting:update-phases --dry-run
```

### Ver Logs

```bash
tail -f storage/logs/laravel.log | grep -i "phase"
```

---

## 5. Archivos Modificados/Creados

### Nuevos Archivos
- ✅ `Modules/JobPosting/database/migrations/2026_01_01_201535_add_duration_hours_to_process_phases_table.php`
- ✅ `Modules/JobPosting/Events/PhaseStarted.php`
- ✅ `Modules/JobPosting/Events/PhaseCompleted.php`
- ✅ `Modules/JobPosting/Events/PhaseDelayed.php`
- ✅ `Modules/JobPosting/Observers/JobPostingObserver.php`
- ✅ `app/Console/Commands/UpdateJobPostingPhasesCommand.php`

### Archivos Modificados
- ✅ `Modules/JobPosting/app/Entities/ProcessPhase.php` - Agregado `default_duration_hours`
- ✅ `Modules/JobPosting/app/Entities/JobPosting.php` - Mejorado `getCurrentPhase()`
- ✅ `Modules/JobPosting/app/Entities/JobPostingSchedule.php` - Eventos en `start()` y `complete()`
- ✅ `Modules/JobPosting/app/Providers/JobPostingServiceProvider.php` - Registro de Observer
- ✅ `routes/console.php` - Configuración del scheduler

---

## 6. Próximos Pasos Recomendados

1. **Crear Listeners** para los eventos de fases (notificaciones, logs, etc.)
2. **Agregar Tests Unitarios** para el comando y los métodos de fase
3. **Dashboard de Monitoreo** para ver el estado de todas las fases en tiempo real
4. **Notificaciones Automáticas** cuando una fase está por comenzar o terminar
5. **Configurar alertas** para fases retrasadas (DELAYED)

---

## 7. Notas Importantes

- ⚠️ **El scheduler debe estar activo** para que las fases se actualicen automáticamente cada hora
- ⚠️ **Los eventos solo se disparan** cuando se usan los métodos `start()` y `complete()` del modelo
- ⚠️ **El fallback de getCurrentPhase()** es útil pero idealmente las fases deberían tener su estado correcto en la BD
- ✅ **El comando es idempotente**: puedes ejecutarlo múltiples veces sin problemas
- ✅ **Usa `--dry-run`** para verificar qué haría el comando antes de ejecutarlo

---

## 8. Soporte

Para dudas o problemas:
1. Revisar logs: `storage/logs/laravel.log`
2. Ejecutar con dry-run: `php artisan jobposting:update-phases --dry-run`
3. Verificar que el scheduler esté activo: `php artisan schedule:list`

---

**Fecha de Implementación**: 2026-01-01
**Versión Laravel**: 12.40.2
**Módulos**: nwidart/laravel-modules
