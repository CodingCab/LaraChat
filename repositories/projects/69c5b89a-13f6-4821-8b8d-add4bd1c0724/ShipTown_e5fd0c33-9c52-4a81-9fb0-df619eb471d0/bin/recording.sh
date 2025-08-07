#!/bin/bash

# set server address and port
SERVER="127.0.0.1"
PORT=8080

# check params
if [ "$#" -lt 1 ]; then
    echo "Usage: $0 start|-stop [options]"
    exit 1
fi

# send command to tcp server
send_command() {
    local command="$1"
    shift  # Remove the first argument (command)

    # Build the command string, encoding spaces in the file parameter
    local args=""
    local in_file_param=false
    for arg in "$@"; do
        if [[ "$arg" == -file=* ]]; then
            # Extract the filename and escape spaces
            local filename="${arg#-file=}"
            filename="${filename// /\\ }"
            args+=" -file=$filename"
            in_file_param=true
        else
            if [ "$in_file_param" = false ]; then
                args+=" $arg"
            fi
        fi
    done

    echo "$command$args" | nc $SERVER $PORT
}

case "$1" in
    start)
        shift
        send_command "start" "$@"
        ;;
    stop)
        # send stop command
        send_command "stop"
        ;;
    *)
        echo "Invalid command. Use 'start' or 'stop'."
        exit 1
        ;;
esac
