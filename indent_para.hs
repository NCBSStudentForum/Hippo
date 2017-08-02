#!/usr/bin/env runhaskell

import Text.Pandoc.JSON


import Data.Array
import Data.List
import Data.Ord

-- | This algorithm split the para at length m.
dilawar :: Int -> String -> String
dilawar m s = dilawar' m (words s) [ ] 0
  where 
    dilawar' m [] result count = unwords result 
    dilawar' m (w:ws) result count  
        | count < m = dilawar' m ws (w : result) (count + (length w))
        | otherwise = dilawar' m ws ( w : "\n " : result ) 0


indent_para [ ] = [ ]
indent_para (LineBreak:xs) = LineBreak : Space : (indent_para xs)
indent_para ((Str x):xs) = (Str (dilawar 75 x)) : (indent_para xs) 
indent_para (x:xs) = x : (indent_para xs) 

main :: IO ( )
main = toJSONFilter filter
  where 
    filter (Para x) = Para (indent_para x)
    filter x = x

