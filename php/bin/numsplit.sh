#!/bin/bash
if [ "$1" = '' ]; then
   echo "Usage: numsplit.sh filename [# of pieces(2)] [# of header lines(1)]"
   exit
else
  file=$1
fi
if [ "$2" = '' ]; then
   num=2
else
  num=$2
fi
if [ "$3" = '' ]; then
   hs=1
else
  hs=$3
fi
t=`cat $file | wc -l`
l=$((t/$num))

echo "File Length = $t, Piece length = $l "
i=1
while [ $i -le $num ]; do
  s=$(($l*$i))
  # Preserve the header line(s)
  if [ $i -gt 1 ]; then
    echo "head -n $hs $file > $file$i"
    head -n $hs $file > "$file$i"
  else
     rm $file$i
  fi
  # append the next chunk
  echo "head -n $s $file | tail -n $l >> $file$i"
  head -n $s $file | tail -n $l >> "$file$i"
  ((i++))
done

