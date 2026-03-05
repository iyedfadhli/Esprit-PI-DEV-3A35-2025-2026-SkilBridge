# Fight Moderation Service (YOLOv8, Image-only)

This service checks uploaded **images** for fight/violence detections.

Endpoint used by Symfony:

- `POST /moderate-image` with multipart field `file`

Response:

```json
{
  "safe": false,
  "reason": "fight_or_violence",
  "confidence": 0.72
}
```

## Run with Docker

From project root:

```bash
docker compose up -d fight_moderation
```

Health:

```bash
curl http://127.0.0.1:8010/health
```

## Config

- `FIGHT_WEIGHTS_URL`: URL to `best.pt`
- `FIGHT_CONFIDENCE`: detection confidence threshold (default `0.35`)
- `FIGHT_CLASS_ID`: class id for fight/violence (default `1`)
