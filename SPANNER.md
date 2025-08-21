# Here are the instructions on how to use Google Cloud Spanner Emulator to develop with Symfony

## Install the gcloud command
This depends on your OS. Have a look at [this link](https://cloud.google.com/sdk/docs/install) for instructions.

## Start up the emulator
```
  ./spanner-emulator-start.sh
```

## Check that the emulator started correctly
```
  ./spanner-emulator-check.sh
```

This should be run on every new console you open so that the environment is correctly set up for the emulator.
