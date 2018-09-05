#!/bin/sh
SPATH=`pwd -P`/`dirname $0`
cat $SPATH/00.database.sql $SPATH/01.structure.sql $SPATH/02.initial_values.sql $SPATH/21.specific-structure.sql $SPATH/22.specific-initial_values.sql > /tmp/import.sql && su postgres -c "psql -f /tmp/import.sql > /tmp/result"
# && rm /tmp/import.sql
