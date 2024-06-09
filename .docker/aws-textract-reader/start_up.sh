#!/bin/bash

# DELETE Old files
rm -f *.json
rm -f *.txt

# Start app
uvicorn server:app --host 0.0.0.0 --app-dir .



