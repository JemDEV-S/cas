"""
Tests para el servicio Career Matcher NLP
"""
import pytest
from app.services.career_matcher import CareerMatcherService


@pytest.fixture
def matcher():
    """Fixture del servicio de matching"""
    return CareerMatcherService(threshold=0.75)


class TestNormalization:
    """Tests para normalización de nombres de carreras"""

    def test_normalize_lowercase(self, matcher):
        result = matcher._normalize_career_name("INGENIERÍA DE SISTEMAS")
        assert result == "ingenieria de sistemas"

    def test_normalize_remove_accents(self, matcher):
        result = matcher._normalize_career_name("Administración")
        assert result == "administracion"

    def test_normalize_expand_abbreviations(self, matcher):
        result = matcher._normalize_career_name("Ing. de Sistemas")
        assert result == "ingenieria de sistemas"

    def test_normalize_multiple_abbreviations(self, matcher):
        result = matcher._normalize_career_name("Lic. en Adm. de Empresas")
        assert result == "licenciatura en administracion de empresas"

    def test_normalize_special_characters(self, matcher):
        result = matcher._normalize_career_name("Ingeniería (Civil)")
        assert "ingenieria" in result
        assert "civil" in result


class TestExactMatch:
    """Tests para coincidencia exacta"""

    def test_exact_match_same_text(self, matcher):
        result = matcher.match_career(
            "Ingeniería de Sistemas",
            ["Ingeniería de Sistemas", "Derecho"]
        )
        assert result["is_match"] is True
        assert result["score"] == 1.0
        assert result["match_type"] == "exact"

    def test_exact_match_different_case(self, matcher):
        result = matcher.match_career(
            "INGENIERÍA DE SISTEMAS",
            ["ingeniería de sistemas"]
        )
        assert result["is_match"] is True
        assert result["score"] == 1.0

    def test_exact_match_with_abbreviation(self, matcher):
        result = matcher.match_career(
            "Ing. de Sistemas",
            ["Ingeniería de Sistemas"]
        )
        assert result["is_match"] is True
        assert result["score"] == 1.0


class TestSynonymMatch:
    """Tests para coincidencia por sinónimos"""

    def test_synonym_sistemas_informatica(self, matcher):
        result = matcher.match_career(
            "Ingeniería de Sistemas",
            ["Ingeniería Informática"]
        )
        assert result["is_match"] is True
        assert result["match_type"] == "synonym"

    def test_synonym_software_sistemas(self, matcher):
        result = matcher.match_career(
            "Ingeniería de Software",
            ["Ingeniería de Sistemas"]
        )
        assert result["is_match"] is True

    def test_synonym_administracion_gestion(self, matcher):
        result = matcher.match_career(
            "Administración de Empresas",
            ["Gestión Empresarial"]
        )
        # Este podría ser match semántico o sinónimo dependiendo de la config
        assert result["is_match"] is True


class TestSemanticMatch:
    """Tests para coincidencia semántica"""

    def test_semantic_related_careers(self, matcher):
        result = matcher.match_career(
            "Ciencias de la Computación",
            ["Ingeniería de Sistemas", "Ingeniería Informática"]
        )
        assert result["is_match"] is True
        assert result["score"] >= 0.75

    def test_semantic_unrelated_careers(self, matcher):
        result = matcher.match_career(
            "Medicina Humana",
            ["Ingeniería de Sistemas", "Contabilidad"]
        )
        assert result["is_match"] is False
        assert result["score"] < 0.75

    def test_semantic_partially_related(self, matcher):
        result = matcher.match_career(
            "Ingeniería Mecatrónica",
            ["Ingeniería Mecánica", "Ingeniería Electrónica"]
        )
        # Debería tener alto score con alguna de las dos
        assert result["score"] >= 0.6


class TestEdgeCases:
    """Tests para casos especiales"""

    def test_empty_candidate(self, matcher):
        result = matcher.match_career("", ["Ingeniería"])
        assert result["is_match"] is False

    def test_empty_accepted_list(self, matcher):
        result = matcher.match_career("Ingeniería", [])
        assert result["is_match"] is False

    def test_single_word_career(self, matcher):
        result = matcher.match_career("Derecho", ["Derecho", "Medicina"])
        assert result["is_match"] is True

    def test_very_long_career_name(self, matcher):
        long_name = "Ingeniería en Tecnologías de la Información y Comunicación con especialidad en Desarrollo de Software"
        result = matcher.match_career(
            long_name,
            ["Ingeniería de Sistemas", "Ingeniería de Software"]
        )
        assert result["is_match"] is True


class TestThreshold:
    """Tests para diferentes umbrales"""

    def test_custom_threshold_lower(self, matcher):
        # Con threshold bajo, más matches
        result = matcher.match_career(
            "Gestión Pública",
            ["Administración Pública"],
            threshold=0.5
        )
        assert result["is_match"] is True

    def test_custom_threshold_higher(self, matcher):
        # Con threshold alto, menos matches
        result = matcher.match_career(
            "Ingeniería Ambiental",
            ["Ingeniería Civil"],
            threshold=0.9
        )
        assert result["is_match"] is False


class TestAllScores:
    """Tests para include_all_scores"""

    def test_all_scores_included(self, matcher):
        result = matcher.match_career(
            "Ingeniería de Sistemas",
            ["Ingeniería Informática", "Derecho", "Medicina"],
            include_all_scores=True
        )
        assert "all_scores" in result
        assert len(result["all_scores"]) == 3
        # Verificar que están ordenados por score descendente
        scores = [s["score"] for s in result["all_scores"]]
        assert scores == sorted(scores, reverse=True)


class TestBatchMatch:
    """Tests para batch processing"""

    def test_batch_multiple_candidates(self, matcher):
        results = matcher.batch_match(
            candidate_careers=["Ing. Sistemas", "Derecho", "Contabilidad"],
            accepted_careers=["Ingeniería de Sistemas", "Ingeniería Informática"]
        )
        assert len(results) == 3
        assert results[0]["is_match"] is True  # Sistemas match
        assert results[1]["is_match"] is False  # Derecho no match
        assert results[2]["is_match"] is False  # Contabilidad no match


class TestRealWorldCases:
    """Tests con casos reales del sistema CAS"""

    @pytest.mark.parametrize("candidate,accepted,should_match", [
        # Casos que DEBEN hacer match
        ("Ingeniería de Software", ["Ingeniería de Sistemas"], True),
        ("Ing. en Computación e Informática", ["Ingeniería Informática"], True),
        ("Ciencias de la Computación", ["Ingeniería de Sistemas"], True),
        ("Contaduría Pública", ["Contabilidad"], True),
        ("Administración de Negocios", ["Administración de Empresas"], True),
        ("Ciencias Jurídicas", ["Derecho"], True),

        # Casos que NO deben hacer match
        ("Medicina Veterinaria", ["Medicina Humana"], False),
        ("Ingeniería Civil", ["Ingeniería de Sistemas"], False),
        ("Psicología", ["Ingeniería Industrial"], False),
        ("Arquitectura", ["Ingeniería Civil"], False),  # Relacionadas pero distintas
    ])
    def test_real_world_case(self, matcher, candidate, accepted, should_match):
        result = matcher.match_career(candidate, accepted)
        assert result["is_match"] == should_match, \
            f"Expected is_match={should_match} for '{candidate}' vs {accepted}, got {result}"
