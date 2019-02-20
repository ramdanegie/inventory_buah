#!/bin/sh
kill $(lsof -t -i:7777)
