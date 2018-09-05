#!/bin/bash
SPATH=`pwd -P`/`dirname $0`
if [ ! -d "$SPATH" ]; then
  SPATH=`dirname $0`/
fi
screen -dmS fads_services $SPATH/services.sh
