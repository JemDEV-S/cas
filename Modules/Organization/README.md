# Organization Module

Módulo de gestión de estructura organizacional jerárquica del sistema CAS-MDSJ.

## 📦 Componentes

### Entidades
- **OrganizationalUnit**: Unidad organizacional con soporte jerárquico completo

### Enums
- **OrganizationalUnitTypeEnum**: Tipos de unidades (Órgano, Área, Sub Unidad)

### Características Jerárquicas
- ✅ Estructura de árbol ilimitada (padres e hijos)
- ✅ Closure Table para queries eficientes
- ✅ Cálculo automático de niveles y paths
- ✅ Obtención rápida de ancestros y descendientes
- ✅ Prevención de ciclos y movimientos inválidos

### Services
- **OrganizationalUnitService**: Gestión completa con lógica jerárquica

### Repositories
- **OrganizationalUnitRepository**: Operaciones especializadas en jerarquías

### Events
- **OrganizationalUnitCreated**: Unidad creada
- **OrganizationalUnitUpdated**: Unidad actualizada
- **OrganizationalUnitDeleted**: Unidad eliminada

## 🚀 Uso

### Crear una unidad organizacional

```php
$service = app(\Modules\Organization\Services\OrganizationalUnitService::class);

// Crear un Órgano (raíz)
$organo = $service->create([
    'code' => 'OGM-001',
    'name' => 'Órgano de Gestión Municipal',
    'description' => 'Órgano principal',
    'type' => 'organo',
    'order' => 1,
    'is_active' => true,
]);

// Crear un Área (hijo)
$area = $service->create([
    'code' => 'AREA-001',
    'name' => 'Área de Recursos Humanos',
    'type' => 'area',
    'parent_id' => $organo->id,
    'order' => 1,
]);
```

### Obtener el árbol completo

```php
$tree = $service->getTree();

// Retorna la estructura jerárquica completa:
// Órgano
//   ├── Área 1
//   │   ├── Sub Unidad 1.1
//   │   └── Sub Unidad 1.2
//   └── Área 2
```

### Obtener ancestros y descendientes

```php
// Obtener todos los ancestros de una unidad
$ancestors = $service->getAncestors($unitId);

// Obtener todos los descendientes
$descendants = $service->getDescendants($unitId);

// Obtener la ruta completa
$unit = OrganizationalUnit::find($id);
echo $unit->full_path;
// Output: "Órgano de Gestión > Área de RRHH > Sub Unidad de Personal"
```

### Validaciones automáticas

El módulo incluye validaciones para:
- ❌ No se puede eliminar una unidad con sub-unidades
- ❌ No se puede mover una unidad que tiene hijos
- ❌ No se permiten códigos duplicados
- ✅ Actualización automática de niveles y paths

## 🗄️ Closure Table

El módulo utiliza el patrón **Closure Table** para queries eficientes:

```
organizational_unit_closure
├── ancestor_id    (UUID)
├── descendant_id  (UUID)
└── depth          (Integer)
```

Esto permite:
- Queries O(1) para obtener ancestros/descendientes
- No requiere recursión en la base de datos
- Escalabilidad para estructuras profundas

## 📊 Estructura de Datos

```
OrganizationalUnit
├── id (UUID)
├── code (Único)
├── name
├── description
├── type (organo|area|sub_unidad)
├── parent_id (Nullable)
├── level (Auto-calculado)
├── path (Auto-calculado, ej: /uuid1/uuid2)
├── order (Orden de visualización)
├── is_active
└── metadata (JSONB)
```

## 🔗 Relaciones

- OrganizationalUnit → belongsTo → Parent (self)
- OrganizationalUnit → hasMany → Children (self)
- OrganizationalUnit → belongsToMany → Ancestors (via closure)
- OrganizationalUnit → belongsToMany → Descendants (via closure)

## 📝 Licencia

Este módulo es parte del sistema CAS-MDSJ
