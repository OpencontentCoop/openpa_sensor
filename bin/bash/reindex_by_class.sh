#!/usr/bin/env bash

ID=$@

php extension/ocsearchtools/bin/php/reindex_by_class.php --allow-root-user -sbackend --class=${ID} > /dev/null &