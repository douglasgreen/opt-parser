# To-do List

1. Fix the way inputs are parsed. Currently the inputs are parsed by the ArgParser completely and
   separated into marked options and unmarked options. This is done without looking at the
   definition of the flags, parameters, and terms.

The problem is if someone wants to pass a flag in front of a term, like `-p VALUE`, where `-p` is
the flag and VALUE is the value. That would be interpreted as a marked option but it should be
interpreted as an unmarked option followed by a term. In order to fix this, I would actually have to
be looking for specific aliases when doing the argument parsing. I would first have to parse the
flags, then the parameters, and finally the command and terms.

2. It might be possible to allow flag combinations like -asp as long as they can't be confused with
   long aliases like --pass.
