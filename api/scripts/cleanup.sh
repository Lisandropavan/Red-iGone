#!/bin/bash

## Some important variables
FIND=/usr/bin/find
FILES=/export/uploads

$FIND $FILES/* -type f -mtime +1 -exec rm -f {} \;