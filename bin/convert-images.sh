#!/usr/bin/env bash
CONTENT_ROOT=$(dirname $(dirname $(realpath $0)))
LANGUAGES=("German" "English")
USER_ID=$(id -u)

for LANG in "${LANGUAGES[@]}"
do
    mkdir -p ${CONTENT_ROOT}/public/api/graphics/${LANG}/
    readarray -d '' GRAPHICS < <(find ${CONTENT_ROOT}/data/tmp/api/graphics/${LANG}/ -type f -iname '*.svg' -print0)
    for GRAPHIC_FILE in "${GRAPHICS[@]}"
    do
      GRAPHIC_NAME=$(basename $GRAPHIC_FILE)
      WINDOW_SIZE=$(grep -o -E "CONVERT_IMAGE_SIZE=[0-9]+,[0-9]+" $GRAPHIC_FILE | cut -d"=" -f2)
      timeout --signal=SIGKILL 60 \
        docker run -u${USER_ID} -v ${CONTENT_ROOT}:/app --entrypoint chrome --rm ghcr.io/montferret/chromium \
          '--user-data-dir=/app/data/tmp/chromeCache' \
          '--disk-cache-dir=/app/data/tmp/chromeCache' \
          '--no-sandbox' \
          '--headless' \
          '--disable-gpu' \
          '--hide-scrollbars' \
          '--window-size='${WINDOW_SIZE} \
          '--screenshot=/app/public/api/graphics/'${LANG}'/'$(basename -s .svg ${GRAPHIC_NAME})'.png' \
          '/app/data/tmp/api/graphics/'${LANG}'/'${GRAPHIC_NAME} \
          2>/dev/null
    done
done
