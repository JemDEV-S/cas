"""
Servicio de matching de carreras usando NLP con Sentence Transformers.

Este servicio compara carreras académicas usando embeddings semánticos
para determinar si una carrera declarada por el postulante es afín
a las carreras requeridas por el perfil del puesto.
"""
import unicodedata
import re
from typing import Optional
from functools import lru_cache
import numpy as np
from sentence_transformers import SentenceTransformer
from sklearn.metrics.pairwise import cosine_similarity

from app.config import get_settings


class CareerMatcherService:
    """
    Servicio para comparar carreras académicas usando similitud semántica.

    Usa Sentence Transformers para generar embeddings de los nombres de carreras
    y calcula la similitud del coseno para determinar matches.
    """

    # Abreviaturas comunes en nombres de carreras peruanas
    ABBREVIATIONS = {
        "ing.": "ingenieria",
        "ing": "ingenieria",
        "lic.": "licenciatura",
        "lic": "licenciatura",
        "adm.": "administracion",
        "adm": "administracion",
        "contab.": "contabilidad",
        "econ.": "economia",
        "der.": "derecho",
        "arq.": "arquitectura",
        "med.": "medicina",
        "enf.": "enfermeria",
        "psic.": "psicologia",
        "educ.": "educacion",
        "com.": "comunicacion",
        "sist.": "sistemas",
        "inf.": "informatica",
        "ind.": "industrial",
        "civ.": "civil",
        "mec.": "mecanica",
        "elec.": "electrica",
        "electron.": "electronica",
        "agr.": "agronomia",
        "vet.": "veterinaria",
        "obs.": "obstetricia",
        "odont.": "odontologia",
        "nutr.": "nutricion",
        "tec.": "tecnico",
        "téc.": "tecnico",
    }

    # Sinónimos de carreras comunes
    CAREER_SYNONYMS = {
        "sistemas": ["informatica", "computacion", "software", "tics", "tecnologias de informacion"],
        "informatica": ["sistemas", "computacion", "software", "tics"],
        "computacion": ["sistemas", "informatica", "software", "ciencias computacionales"],
        "software": ["sistemas", "informatica", "desarrollo de software"],
        "administracion": ["gestion", "gerencia", "direccion de empresas", "negocios"],
        "contabilidad": ["contaduria", "ciencias contables", "auditoria"],
        "derecho": ["ciencias juridicas", "leyes", "jurisprudencia"],
        "economia": ["ciencias economicas", "finanzas"],
        "industrial": ["produccion", "manufactura", "procesos industriales"],
        "civil": ["construccion", "estructuras", "obras civiles"],
        "electronica": ["telecomunicaciones", "electronica y telecomunicaciones"],
        "mecanica": ["mecanica industrial", "maquinaria"],
        "ambiental": ["medio ambiente", "ecologia", "gestion ambiental"],
        "agronomia": ["agropecuaria", "agricultura", "ciencias agrarias"],
    }

    def __init__(self, model_name: Optional[str] = None, threshold: Optional[float] = None):
        """
        Inicializa el servicio con el modelo de embeddings.

        Args:
            model_name: Nombre del modelo de Sentence Transformers
            threshold: Umbral mínimo de similitud para considerar match
        """
        settings = get_settings()
        self.model_name = model_name or settings.model_name
        self.threshold = threshold or settings.default_threshold
        self._model: Optional[SentenceTransformer] = None

    @property
    def model(self) -> SentenceTransformer:
        """Lazy loading del modelo para evitar carga en import"""
        if self._model is None:
            self._model = SentenceTransformer(self.model_name)
        return self._model

    def match_career(
        self,
        candidate_career: str,
        accepted_careers: list[str],
        threshold: Optional[float] = None,
        include_all_scores: bool = False
    ) -> dict:
        """
        Compara una carrera candidata contra las carreras aceptadas.

        Args:
            candidate_career: Nombre de la carrera del postulante
            accepted_careers: Lista de carreras aceptadas por el perfil
            threshold: Umbral de similitud (usa default si no se especifica)
            include_all_scores: Si incluir scores de todas las comparaciones

        Returns:
            dict con:
                - is_match: bool indicando si hay match
                - score: float con el mejor score de similitud
                - matched_career: str con la carrera que hizo match (o None)
                - all_scores: list de scores si include_all_scores=True
        """
        if not candidate_career or not accepted_careers:
            return {
                "is_match": False,
                "score": 0.0,
                "matched_career": None,
                "reason": "Datos de entrada vacíos"
            }

        threshold = threshold or self.threshold

        # Normalizar textos
        candidate_normalized = self._normalize_career_name(candidate_career)
        accepted_normalized = [self._normalize_career_name(c) for c in accepted_careers]

        # Verificar match exacto después de normalización
        for i, accepted in enumerate(accepted_normalized):
            if candidate_normalized == accepted:
                return {
                    "is_match": True,
                    "score": 1.0,
                    "matched_career": accepted_careers[i],
                    "match_type": "exact",
                    "reason": "Coincidencia exacta después de normalización"
                }

        # Verificar match por sinónimos
        synonym_match = self._check_synonym_match(candidate_normalized, accepted_normalized, accepted_careers)
        if synonym_match:
            return synonym_match

        # Generar embeddings y calcular similitud
        try:
            # Combinar todos los textos para encoding batch
            all_texts = [candidate_normalized] + accepted_normalized
            embeddings = self.model.encode(all_texts, convert_to_numpy=True)

            # Separar embedding del candidato y de las carreras aceptadas
            candidate_embedding = embeddings[0:1]
            accepted_embeddings = embeddings[1:]

            # Calcular similitudes del coseno
            similarities = cosine_similarity(candidate_embedding, accepted_embeddings)[0]

            # Encontrar mejor match
            best_idx = int(np.argmax(similarities))
            best_score = float(similarities[best_idx])

            result = {
                "is_match": best_score >= threshold,
                "score": round(best_score, 4),
                "matched_career": accepted_careers[best_idx] if best_score >= threshold else None,
                "match_type": "semantic" if best_score >= threshold else None,
                "threshold_used": threshold,
                "reason": f"Similitud semántica: {best_score:.2%}"
            }

            if include_all_scores:
                result["all_scores"] = [
                    {
                        "career": accepted_careers[i],
                        "score": round(float(similarities[i]), 4)
                    }
                    for i in range(len(accepted_careers))
                ]
                # Ordenar por score descendente
                result["all_scores"].sort(key=lambda x: x["score"], reverse=True)

            return result

        except Exception as e:
            return {
                "is_match": False,
                "score": 0.0,
                "matched_career": None,
                "error": str(e),
                "reason": f"Error en procesamiento NLP: {str(e)}"
            }

    def _normalize_career_name(self, name: str) -> str:
        """
        Normaliza el nombre de una carrera para mejor comparación.

        - Convierte a minúsculas
        - Remueve tildes/acentos
        - Expande abreviaturas
        - Remueve caracteres especiales
        - Normaliza espacios
        """
        if not name:
            return ""

        # Convertir a minúsculas
        text = name.lower().strip()

        # Remover tildes y caracteres diacríticos
        text = unicodedata.normalize('NFKD', text)
        text = ''.join(c for c in text if not unicodedata.combining(c))

        # Expandir abreviaturas
        for abbr, full in self.ABBREVIATIONS.items():
            # Usar word boundaries para evitar reemplazos parciales
            text = re.sub(rf'\b{re.escape(abbr)}\b', full, text)

        # Remover caracteres especiales excepto espacios
        text = re.sub(r'[^\w\s]', ' ', text)

        # Normalizar espacios múltiples
        text = re.sub(r'\s+', ' ', text).strip()

        return text

    def _check_synonym_match(
        self,
        candidate: str,
        accepted_normalized: list[str],
        accepted_original: list[str]
    ) -> Optional[dict]:
        """
        Verifica si hay match por sinónimos conocidos.

        Returns:
            dict con resultado del match si se encuentra, None si no
        """
        # Extraer palabras clave de la carrera candidata
        candidate_words = set(candidate.split())

        for i, accepted in enumerate(accepted_normalized):
            accepted_words = set(accepted.split())

            # Verificar si alguna palabra clave tiene sinónimos en común
            for cword in candidate_words:
                if cword in self.CAREER_SYNONYMS:
                    synonyms = set(self.CAREER_SYNONYMS[cword])
                    # Si algún sinónimo está en las palabras de la carrera aceptada
                    if synonyms & accepted_words:
                        return {
                            "is_match": True,
                            "score": 0.90,  # Score alto pero no perfecto
                            "matched_career": accepted_original[i],
                            "match_type": "synonym",
                            "reason": f"Match por sinónimo: '{cword}' relacionado con carrera aceptada"
                        }

            # Verificar inverso: palabra de accepted tiene sinónimo en candidate
            for aword in accepted_words:
                if aword in self.CAREER_SYNONYMS:
                    synonyms = set(self.CAREER_SYNONYMS[aword])
                    if synonyms & candidate_words:
                        return {
                            "is_match": True,
                            "score": 0.90,
                            "matched_career": accepted_original[i],
                            "match_type": "synonym",
                            "reason": f"Match por sinónimo inverso: '{aword}' relacionado"
                        }

        return None

    def batch_match(
        self,
        candidate_careers: list[str],
        accepted_careers: list[str],
        threshold: Optional[float] = None
    ) -> list[dict]:
        """
        Procesa múltiples carreras candidatas en batch.

        Args:
            candidate_careers: Lista de carreras a evaluar
            accepted_careers: Lista de carreras aceptadas
            threshold: Umbral de similitud

        Returns:
            Lista de resultados para cada carrera candidata
        """
        return [
            self.match_career(candidate, accepted_careers, threshold)
            for candidate in candidate_careers
        ]


# Singleton para reutilizar el modelo cargado
@lru_cache()
def get_career_matcher() -> CareerMatcherService:
    """Obtiene instancia singleton del servicio"""
    return CareerMatcherService()
