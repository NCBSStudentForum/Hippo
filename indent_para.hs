#!/usr/bin/runhaskell

import Text.Pandoc.JSON

indent_para LineBreak = Str "\n  " 
indent_para x = x

main :: IO ( )
main = toJSONFilter filter
  where 
    filter (Para x) = Para (map indent_para x)
    filter x = x

