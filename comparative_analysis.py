import json
import re
import sys
from difflib import SequenceMatcher

# Fix encoding for Windows console
if sys.platform == 'win32':
    sys.stdout.reconfigure(encoding='utf-8')

# Cargar datos
with open('sunedu_analysis.json', 'r', encoding='utf-8') as f:
    sunedu_data = json.load(f)

with open('job_profiles_careers.json', 'r', encoding='utf-8') as f:
    job_profiles_data = json.load(f)

# Extraer lista de carreras SUNEDU (todas las carreras únicas de todas las categorías)
sunedu_careers = set()
for categoria, data in sunedu_data['carreras_por_categoria'].items():
    sunedu_careers.update(data['carreras_unicas'])

# Normalizar función
def normalize(text):
    if not text:
        return ""
    # Convertir a mayúsculas
    text = text.upper()
    # Eliminar tildes
    replacements = {
        'Á': 'A', 'É': 'E', 'Í': 'I', 'Ó': 'O', 'Ú': 'U',
        'Ñ': 'N'
    }
    for old, new in replacements.items():
        text = text.replace(old, new)
    # Quitar espacios extras
    text = ' '.join(text.split())
    return text

# Función para extraer carreras individuales de strings combinados
def extract_individual_careers(career_string):
    """
    Extrae carreras individuales de strings como:
    - "CONTABILIDAD, ADMINISTRACION O ECONOMIA"
    - "INGENIERIA DE SISTEMAS O AFINES"
    """
    # Normalizar
    career_string = normalize(career_string)

    # Eliminar frases genéricas
    career_string = re.sub(r'\bO\s+AFINES\b', '', career_string)
    career_string = re.sub(r'\bAFINES\b', '', career_string)
    career_string = re.sub(r'\bCARRERA\s+DE\b', '', career_string)
    career_string = re.sub(r'\bCARRERA\s+PROFESIONAL\s+DE\b', '', career_string)
    career_string = re.sub(r'\bEGRESADO\s+.*', '', career_string)

    # Separar por comas y "O"
    parts = re.split(r'[,\s]+O\s+|,\s*', career_string)

    # Limpiar y filtrar
    careers = []
    for part in parts:
        part = part.strip()
        if part and len(part) > 3 and part not in ['O', 'DE', 'LA', 'Y', 'EN']:
            careers.append(part)

    return careers

# Análisis de job_profiles
job_profile_careers = job_profiles_data['carreras']

print("=" * 80)
print("ANÁLISIS COMPARATIVO: SUNEDU vs JOB_PROFILES")
print("=" * 80)

print(f"\n1. ESTADÍSTICAS GENERALES")
print(f"   - Carreras SUNEDU (pregrado): {len(sunedu_careers)}")
print(f"   - Carreras en job_profiles (strings únicos): {len(job_profile_careers)}")

# Extraer carreras individuales de job_profiles
all_individual_careers = set()
career_mapping = {}  # career_string -> [individual_careers]

for career_str in job_profile_careers:
    individual = extract_individual_careers(career_str)
    all_individual_careers.update(individual)
    career_mapping[career_str] = individual

print(f"   - Carreras individuales extraídas: {len(all_individual_careers)}")

# Encontrar matches
def find_best_match(career, sunedu_list, threshold=0.7):
    """Encuentra el mejor match en la lista SUNEDU"""
    career_norm = normalize(career)
    best_match = None
    best_score = 0

    for sunedu_career in sunedu_list:
        sunedu_norm = normalize(sunedu_career)

        # Exact match
        if career_norm == sunedu_norm:
            return sunedu_career, 1.0

        # Contains
        if career_norm in sunedu_norm or sunedu_norm in career_norm:
            score = max(len(career_norm), len(sunedu_norm)) / max(len(career_norm), len(sunedu_norm))
            if score > best_score:
                best_score = score
                best_match = sunedu_career

        # Similarity
        similarity = SequenceMatcher(None, career_norm, sunedu_norm).ratio()
        if similarity > best_score:
            best_score = similarity
            best_match = sunedu_career

    if best_score >= threshold:
        return best_match, best_score
    return None, 0

# Analizar matches
matches = {}
no_matches = []

print(f"\n2. ANÁLISIS DE MATCHES CON SUNEDU")
print(f"   Buscando correspondencias (threshold >= 70%)...")

for career in sorted(all_individual_careers):
    match, score = find_best_match(career, sunedu_careers, threshold=0.7)
    if match:
        matches[career] = {'sunedu_match': match, 'score': score}
    else:
        no_matches.append(career)

print(f"\n   ✓ Careers con match: {len(matches)} ({len(matches)/len(all_individual_careers)*100:.1f}%)")
print(f"   ✗ Careers sin match: {len(no_matches)} ({len(no_matches)/len(all_individual_careers)*100:.1f}%)")

# Top matches
print(f"\n3. TOP 20 MATCHES")
sorted_matches = sorted(matches.items(), key=lambda x: x[1]['score'], reverse=True)
for i, (career, data) in enumerate(sorted_matches[:20], 1):
    print(f"   {i:2d}. {career:50s} → {data['sunedu_match']:50s} ({data['score']*100:.1f}%)")

# Carreras sin match
print(f"\n4. CARRERAS SIN MATCH EN SUNEDU (requieren creación manual)")
for i, career in enumerate(sorted(no_matches), 1):
    print(f"   {i:2d}. {career}")

# Análisis por categoría
print(f"\n5. CARRERAS MÁS FRECUENTES EN JOB_PROFILES")
career_freq = {}
for career_str in job_profile_careers:
    for individual in career_mapping[career_str]:
        career_freq[individual] = career_freq.get(individual, 0) + 1

sorted_freq = sorted(career_freq.items(), key=lambda x: x[1], reverse=True)
print(f"   Top 20 carreras más usadas:")
for i, (career, freq) in enumerate(sorted_freq[:20], 1):
    match_info = ""
    if career in matches:
        match_info = f" → {matches[career]['sunedu_match']}"
    print(f"   {i:2d}. {career:40s} ({freq:2d} perfiles){match_info}")

# Recomendaciones
print(f"\n{'=' * 80}")
print("6. RECOMENDACIONES PARA LA ARQUITECTURA")
print(f"{'=' * 80}")

print(f"\n✓ Crear catálogo base con las {len(sorted_freq[:30])} carreras más usadas")
print(f"✓ Usar tabla de sinónimos para mapear variantes desde SUNEDU")
print(f"✓ {len(no_matches)} carreras requieren creación manual (no están en SUNEDU)")
print(f"✓ Normalizar {len(all_individual_careers)} carreras individuales vs {len(job_profile_careers)} strings combinados")

# Guardar análisis
output = {
    'total_individual_careers': len(all_individual_careers),
    'total_job_profile_strings': len(job_profile_careers),
    'matches': len(matches),
    'no_matches': len(no_matches),
    'match_percentage': len(matches)/len(all_individual_careers)*100,
    'top_20_frequent': [(c, f) for c, f in sorted_freq[:20]],
    'careers_without_sunedu_match': sorted(no_matches),
    'all_matches': matches
}

with open('comparative_analysis.json', 'w', encoding='utf-8') as f:
    json.dump(output, f, ensure_ascii=False, indent=2)

print(f"\n✓ Análisis guardado en: comparative_analysis.json")
