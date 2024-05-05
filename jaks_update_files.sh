#!/bin/bash

# Directory containing the files
directory="/home/ko58/git/JAKS"

# Output text file
output_file="/home/ko58/git/JAKS/file_history.txt"

# List file names and append them to the text file
ls "$directory" > "$output_file"
