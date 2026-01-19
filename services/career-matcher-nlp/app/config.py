"""
Configuración del servicio Career Matcher NLP
"""
from pydantic_settings import BaseSettings
from functools import lru_cache


class Settings(BaseSettings):
    """Configuración de la aplicación"""

    # App
    app_name: str = "Career Matcher NLP Service"
    app_version: str = "1.0.0"
    debug: bool = False

    # NLP Model
    model_name: str = "paraphrase-multilingual-MiniLM-L12-v2"
    default_threshold: float = 0.75

    # Cache
    cache_enabled: bool = True
    cache_ttl_seconds: int = 86400  # 24 horas

    # Server
    host: str = "0.0.0.0"
    port: int = 8000

    class Config:
        env_file = ".env"
        env_prefix = "CAREER_MATCHER_"


@lru_cache()
def get_settings() -> Settings:
    """Obtiene la configuración cacheada"""
    return Settings()
