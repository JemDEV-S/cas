import csv
import json
from collections import defaultdict, Counter

# Leer el CSV (intentar diferentes encodings)
data = []
encodings = ['utf-8', 'latin-1', 'cp1252', 'iso-8859-1']
for encoding in encodings:
    try:
        with open('Programas de Universidades_8.csv', 'r', encoding=encoding) as f:
            reader = csv.DictReader(f, delimiter='|')
            for row in reader:
                data.append(row)
        print(f"Archivo leído con encoding: {encoding}\n")
        break
    except UnicodeDecodeError:
        data = []
        continue

print(f"=== ANÁLISIS DATASET SUNEDU ===")
print(f"Total registros: {len(data)}\n")

# 1. Distribución por tipo académico
print("=== 1. DISTRIBUCIÓN POR TIPO ACADÉMICO ===")
tipo_academico = Counter([row['TIPO_NIVEL_ACADEMICO'] for row in data])
for tipo, count in tipo_academico.items():
    print(f"{tipo}: {count}")

# 2. Distribución por nivel académico
print("\n=== 2. DISTRIBUCIÓN POR NIVEL ACADÉMICO ===")
nivel_academico = Counter([row['NIVEL_ACADEMICO'] for row in data])
for nivel, count in sorted(nivel_academico.items(), key=lambda x: x[1], reverse=True):
    print(f"{nivel}: {count}")

# 3. PREGRADO - Carreras únicas
print("\n=== 3. PREGRADO - CARRERAS PROFESIONALES ===")
pregrado = [row for row in data if row['TIPO_NIVEL_ACADEMICO'] == 'PREGRADO']
print(f"Total registros PREGRADO: {len(pregrado)}")

carreras_pregrado = Counter([row['DENOMINACION_PROGRAMA'] for row in pregrado])
print(f"Carreras únicas en PREGRADO: {len(carreras_pregrado)}")
print(f"\nTop 30 carreras más comunes:")
for carrera, count in carreras_pregrado.most_common(30):
    print(f"  {carrera}: {count} programas")

# 4. Categorías SUNEDU
print("\n=== 4. CATEGORÍAS SUNEDU (NOMBRE_CLASE_PROGRAMA_N2) ===")
categorias = Counter([row['NOMBRE_CLASE_PROGRAMA_N2'] for row in pregrado])
print(f"Total categorías: {len(categorias)}")
for categoria, count in sorted(categorias.items(), key=lambda x: x[1], reverse=True):
    print(f"  {categoria}: {count} programas")

# 5. Análisis por categoría
print("\n=== 5. CARRERAS POR CATEGORÍA (Top 5 categorías) ===")
carreras_por_categoria = defaultdict(set)
for row in pregrado:
    carreras_por_categoria[row['NOMBRE_CLASE_PROGRAMA_N2']].add(row['DENOMINACION_PROGRAMA'])

for categoria, count in list(categorias.most_common(5)):
    print(f"\n{categoria} ({count} programas):")
    carreras = sorted(carreras_por_categoria[categoria])
    for i, carrera in enumerate(carreras[:10], 1):
        print(f"  {i}. {carrera}")
    if len(carreras) > 10:
        print(f"  ... y {len(carreras) - 10} más")

# 6. Guardar todas las carreras únicas en JSON
output = {
    'total_registros': len(data),
    'pregrado_total': len(pregrado),
    'carreras_unicas': len(carreras_pregrado),
    'categorias_totales': len(categorias),
    'carreras_por_categoria': {}
}

for categoria in sorted(carreras_por_categoria.keys()):
    output['carreras_por_categoria'][categoria] = {
        'total_programas': categorias[categoria],
        'carreras_unicas': sorted(list(carreras_por_categoria[categoria]))
    }

with open('sunedu_analysis.json', 'w', encoding='utf-8') as f:
    json.dump(output, f, ensure_ascii=False, indent=2)

print("\n=== ANÁLISIS GUARDADO EN: sunedu_analysis.json ===")
