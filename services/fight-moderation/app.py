from __future__ import annotations

import os
import tempfile
import urllib.request
from pathlib import Path
from typing import Any

from fastapi import FastAPI, File, HTTPException, UploadFile
from ultralytics import YOLO

app = FastAPI(title="Fight Moderation Service", version="1.0.0")

MODEL_PATH = os.getenv("FIGHT_WEIGHTS_PATH", "/app/models/best.pt")
MODEL_URL = os.getenv(
    "FIGHT_WEIGHTS_URL",
    "https://raw.githubusercontent.com/Musawer1214/Fight-Violence-detection-yolov8/main/Yolo_nano_weights.pt",
)
CONF_THRESHOLD = float(os.getenv("FIGHT_CONFIDENCE", "0.35"))
TARGET_CLASS = int(os.getenv("FIGHT_CLASS_ID", "1"))

_model = None


def _ensure_model_file() -> str:
    path = Path(MODEL_PATH)
    path.parent.mkdir(parents=True, exist_ok=True)
    if path.exists():
        return str(path)

    if not MODEL_URL:
        raise RuntimeError("FIGHT_WEIGHTS_URL is not set and weights file is missing.")

    urllib.request.urlretrieve(MODEL_URL, str(path))
    return str(path)


def _get_model() -> YOLO:
    global _model
    if _model is None:
        model_path = _ensure_model_file()
        _model = YOLO(model_path)
    return _model


@app.get("/health")
def health() -> dict[str, str]:
    return {"status": "ok"}


@app.post("/moderate-image")
async def moderate_image(file: UploadFile = File(...)) -> dict[str, Any]:
    if not file.content_type or not file.content_type.startswith("image/"):
        raise HTTPException(status_code=415, detail="Only image files are supported.")

    suffix = Path(file.filename or "upload.jpg").suffix or ".jpg"
    with tempfile.NamedTemporaryFile(delete=False, suffix=suffix) as tmp:
        tmp_path = Path(tmp.name)
        tmp.write(await file.read())

    try:
        model = _get_model()
        result = model.predict(
            source=str(tmp_path),
            conf=CONF_THRESHOLD,
            classes=[TARGET_CLASS],
            verbose=False,
        )[0]

        boxes = result.boxes
        if boxes is None or len(boxes) == 0:
            return {"safe": True, "reason": "", "confidence": 0.0}

        confidence = float(max(boxes.conf).item()) if boxes.conf is not None else 0.0
        return {
            "safe": False,
            "reason": "fight_or_violence",
            "confidence": max(0.0, min(1.0, confidence)),
        }
    finally:
        try:
            tmp_path.unlink(missing_ok=True)
        except Exception:
            pass
