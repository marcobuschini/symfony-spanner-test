#!/bin/sh

export SPANNER_EMULATOR_HOST=localhost:9010

gcloud config set project your-project-id

gcloud spanner instances create emulator-instance --config=emulator --description=Emulator

gcloud spanner databases create emulator-database --instance emulator-instance

gcloud spanner databases execute-sql emulator-database --instance=emulator-instance --sql="SELECT * FROM INFORMATION_SCHEMA.Tables"