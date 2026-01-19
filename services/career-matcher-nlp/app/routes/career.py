"""
Endpoints para el servicio de matching de carreras.
"""
from typing import Optional
from fastapi import APIRouter, HTTPException
from pydantic import BaseModel, Field

from app.services.career_matcher import get_career_matcher

router = APIRouter(prefix="/api/v1", tags=["career"])


class CareerMatchRequest(BaseModel):
    """Request para comparar una carrera candidata"""
    candidate_career: str = Field(
        ...,
        min_length=2,
        max_length=500,
        description="Nombre de la carrera del postulante",
        examples=["Ingeniería de Software"]
    )
    accepted_careers: list[str] = Field(
        ...,
        min_length=1,
        max_length=50,
        description="Lista de carreras aceptadas por el perfil",
        examples=[["Ingeniería de Sistemas", "Ingeniería Informática", "Ciencias de la Computación"]]
    )
    threshold: Optional[float] = Field(
        default=None,
        ge=0.0,
        le=1.0,
        description="Umbral de similitud (0-1). Si no se especifica, usa el default del servicio"
    )
    include_all_scores: bool = Field(
        default=False,
        description="Si incluir scores de todas las comparaciones"
    )


class CareerScore(BaseModel):
    """Score de similitud para una carrera"""
    career: str
    score: float


class CareerMatchResponse(BaseModel):
    """Response del matching de carreras"""
    is_match: bool = Field(..., description="Si la carrera candidata hace match")
    score: float = Field(..., ge=0.0, le=1.0, description="Score de similitud del mejor match")
    matched_career: Optional[str] = Field(None, description="Carrera que hizo match")
    match_type: Optional[str] = Field(None, description="Tipo de match: exact, synonym, semantic")
    threshold_used: Optional[float] = Field(None, description="Umbral usado para la comparación")
    reason: Optional[str] = Field(None, description="Explicación del resultado")
    all_scores: Optional[list[CareerScore]] = Field(None, description="Scores de todas las comparaciones")


class BatchMatchRequest(BaseModel):
    """Request para comparar múltiples carreras"""
    candidate_careers: list[str] = Field(
        ...,
        min_length=1,
        max_length=100,
        description="Lista de carreras candidatas a evaluar"
    )
    accepted_careers: list[str] = Field(
        ...,
        min_length=1,
        max_length=50,
        description="Lista de carreras aceptadas"
    )
    threshold: Optional[float] = Field(default=None, ge=0.0, le=1.0)


class BatchMatchResponse(BaseModel):
    """Response del batch matching"""
    results: list[CareerMatchResponse]
    total: int
    matched: int
    match_rate: float


@router.post(
    "/match-career",
    response_model=CareerMatchResponse,
    summary="Comparar carrera candidata",
    description="Compara una carrera declarada por el postulante contra las carreras aceptadas usando NLP"
)
async def match_career(request: CareerMatchRequest) -> CareerMatchResponse:
    """
    Evalúa si una carrera candidata es afín a las carreras aceptadas.

    Usa embeddings semánticos para determinar similitud entre:
    - La carrera declarada por el postulante
    - Las carreras requeridas por el perfil del puesto

    El servicio primero intenta match exacto, luego por sinónimos,
    y finalmente por similitud semántica usando el modelo NLP.
    """
    try:
        matcher = get_career_matcher()
        result = matcher.match_career(
            candidate_career=request.candidate_career,
            accepted_careers=request.accepted_careers,
            threshold=request.threshold,
            include_all_scores=request.include_all_scores
        )

        return CareerMatchResponse(**result)

    except Exception as e:
        raise HTTPException(
            status_code=500,
            detail=f"Error procesando la solicitud: {str(e)}"
        )


@router.post(
    "/batch-match",
    response_model=BatchMatchResponse,
    summary="Comparar múltiples carreras",
    description="Procesa múltiples carreras candidatas en una sola solicitud"
)
async def batch_match(request: BatchMatchRequest) -> BatchMatchResponse:
    """
    Evalúa múltiples carreras candidatas contra las carreras aceptadas.

    Útil para procesar lotes de postulantes o validar múltiples
    formaciones académicas de un mismo postulante.
    """
    try:
        matcher = get_career_matcher()
        results = matcher.batch_match(
            candidate_careers=request.candidate_careers,
            accepted_careers=request.accepted_careers,
            threshold=request.threshold
        )

        response_results = [CareerMatchResponse(**r) for r in results]
        matched_count = sum(1 for r in response_results if r.is_match)

        return BatchMatchResponse(
            results=response_results,
            total=len(response_results),
            matched=matched_count,
            match_rate=round(matched_count / len(response_results), 4) if response_results else 0.0
        )

    except Exception as e:
        raise HTTPException(
            status_code=500,
            detail=f"Error procesando la solicitud: {str(e)}"
        )


@router.get(
    "/normalize",
    summary="Normalizar nombre de carrera",
    description="Normaliza un nombre de carrera (útil para debugging)"
)
async def normalize_career(career: str) -> dict:
    """Normaliza un nombre de carrera para ver cómo se procesa"""
    matcher = get_career_matcher()
    normalized = matcher._normalize_career_name(career)
    return {
        "original": career,
        "normalized": normalized
    }
