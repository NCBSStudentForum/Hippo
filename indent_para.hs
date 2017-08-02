#!/usr/bin/runhaskell

import Text.Pandoc.JSON

indent_para [ ] = [ ]
indent_para (LineBreak:xs) = LineBreak : Space : (indent_para xs)
indent_para (x:xs) = x : (indent_para xs) 

main :: IO ( )
main = toJSONFilter filter
  where 
    filter (Para x) = Para (indent_para x)
    filter x = x

