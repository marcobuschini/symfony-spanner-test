# Hera are instructions on how to use Google Cloud Spanner Emulator do develop with Symfony

## Install the docker image for the emulator

## Start up the emulator
```gcloud emulators spanner start```

## Create a and instance in the emulator
```gcloud spanner instances create emulator-instance --config=emulator --description=Emulator```

## Create a database in the instance
```gcloud spanner databases create emulator-database --instance emulator-instance```

## To test for the emulator to work
```gcloud spanner databases execute-sql emulator-database --instance=emulator-instance --sql="SELECT * FROM INFORMATION_SCHEMA.Tables"```