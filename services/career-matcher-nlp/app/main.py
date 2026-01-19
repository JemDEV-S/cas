"""
Career Matcher NLP Service

Microservicio para comparar carreras académicas usando procesamiento
de lenguaje natural con Sentence Transformers.

Este servicio es consumido por el sistema CAS para validar carreras
afines declaradas por postulantes.
"""
from contextlib import asynccontextmanager
from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware

from app.config import get_settings
from app.routes import career_router
from app.services.career_matcher import get_career_matcher


@asynccontextmanager
async def lifespan(app: FastAPI):
    """
    Lifecycle manager para cargar el modelo al iniciar.

    Pre-carga el modelo de Sentence Transformers para que las
    primeras requests no tengan latencia de carga.
    """
    settings = get_settings()
    print(f"Iniciando {settings.app_name} v{settings.app_version}")
    print(f"Cargando modelo NLP: {settings.model_name}...")

    # Pre-cargar el modelo
    matcher = get_career_matcher()
    _ = matcher.model  # Trigger lazy loading

    print("Modelo NLP cargado exitosamente")
    print(f"Threshold por defecto: {settings.default_threshold}")

    yield

    print("Cerrando servicio...")


# Crear aplicación FastAPI
settings = get_settings()

app = FastAPI(
    title=settings.app_name,
    version=settings.app_version,
    description="""
## Career Matcher NLP Service

Servicio de procesamiento de lenguaje natural para comparar carreras académicas.

### Funcionalidades:
- **Match semántico**: Usa embeddings para encontrar similitud entre carreras
- **Normalización**: Expande abreviaturas y normaliza texto
- **Sinónimos**: Base de conocimiento de carreras equivalentes
- **Batch processing**: Procesar múltiples carreras en una solicitud

### Uso típico:
El sistema CAS llama a este servicio cuando un postulante declara una
"carrera afín" que no está en el catálogo, para determinar si es válida.
    """,
    docs_url="/docs",
    redoc_url="/redoc",
    lifespan=lifespan
)

# Configurar CORS para permitir llamadas desde Laravel
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # En producción, restringir a dominios específicos
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Registrar rutas
app.include_router(career_router)


@app.get("/", tags=["root"])
async def root():
    """Endpoint raíz con información del servicio"""
    return {
        "service": settings.app_name,
        "version": settings.app_version,
        "status": "running",
        "docs": "/docs"
    }


@app.get("/health", tags=["health"])
async def health_check():
    """Health check para monitoreo y load balancers"""
    try:
        # Verificar que el modelo está cargado
        matcher = get_career_matcher()
        model_loaded = matcher._model is not None

        return {
            "status": "healthy",
            "model_loaded": model_loaded,
            "model_name": settings.model_name,
            "threshold": settings.default_threshold
        }
    except Exception as e:
        return {
            "status": "unhealthy",
            "error": str(e)
        }


@app.get("/ready", tags=["health"])
async def readiness_check():
    """
    Readiness check - verifica que el servicio está listo para recibir requests.

    Diferente de health: ready verifica que el modelo está cargado.
    """
    try:
        matcher = get_career_matcher()
        # Hacer una pequeña prueba
        result = matcher.match_career(
            "ingenieria de sistemas",
            ["ingenieria informatica"]
        )
        return {
            "ready": True,
            "test_result": result.get("is_match", False)
        }
    except Exception as e:
        return {
            "ready": False,
            "error": str(e)
        }


if __name__ == "__main__":
    import uvicorn
    uvicorn.run(
        "app.main:app",
        host=settings.host,
        port=settings.port,
        reload=settings.debug
    )
