#!/usr/bin/env python3

import fileinput
import re


tokenizer = re.compile('(.*?)((?<!\\\\)")(.*)((?<!\\\\)")(.*)')
replace = re.compile('(?<!\\\\)\'')

is_value = False
for line in fileinput.input():
    tokens = tokenizer.split(line)

    for token in tokens:
        if is_value:
            token = replace.sub("\\'", token)

        if '"' == token:
            is_value = False if is_value else True
            token = "'"

        print(token, end='')
