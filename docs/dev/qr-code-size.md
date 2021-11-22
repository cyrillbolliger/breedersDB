# Size of QR-Code

## QR-Code Sizes

Width and height in px in relation to the lenght of the code's data and the zoom level (assumed there are lower case chars in the data).

| # chars | Zoom 1 | Zoom 2 | Zoom 3 | Zoom 4 | Zoom 5 | Zoom 6 | Zoom 7 | Zoom 8 | Zoom 9 | Zoom 10 |
| --: | --: | --: | --: | --: | --: | --: | --: | --: | --: | --: |
| <1 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 |
| <9 | 21 | 42 | 63 | 83 | 104 | 125 | 147 | 168 | 189 | 210 |
| <19 | 25 | 50 | 75 | 99 | 124 | 149 | 175 | 200 | 225 | 250 |
| <33 | 28 | 58 | 86 | 115 | 143 | 173 | 202 | 230 | 259 | 289 |
| <48 | 32 | 66 | 98 | 131 | 163 | 197 | 230 | 262 | 295 | 329 |
| <62 | 37 | 75 | 111 | 147 | 183 | 221 | 258 | 295 | 332 | 370 |
| <83 | 41 | 83 | 123 | 163 | 203 | 245 | 287 | 328 | 369 | 411 |
| <92 | 45 | 91 | 135 | 178 | 222 | 268 | 314 | 359 | 404 | 450 |
| <121 | 49 | 99 | 147 | 194 | 242 | 292 | 342 | 391 | 440 | 490 |


## Testconditions
- [labelary.com](http://labelary.com/viewer.html)
- Print Density: 8dpmm (203 dpi)
- Label Size: 50x50mm


## Size depending on content length

### Testcode
Execute the following snipped to generate the test code:
```python
#!/bin/python3

for i in range(1,130):
  data = 'X'*i
  data = 's'+data[1:]
  
  print("^XA^LH0,0^BY0,0,0^FO0,0^BQ,2,7^FDH,"+data+"^FS^A0,36^FO0,350^FD"+str(i)+"^XZ")

  if ((i+1)%50 == 0):
    print("--- split here (labelary.com only accepts 50 at once) ---")
```

### Measured size
| Character limit | Edge length (px) |
| --: | --: |
| <1 | 0 |
| <9 | 147 |
| <19 | 175 |
| <33 | 202 |
| <48 | 230 |
| <62 | 258 |
| <83 | 287 |
| <92 | 314 |
| <121 | 342 |

Only 121 chars were measured.


**Note**: If the code's data does not contain lower case characters, the boundaries are slightly higher:
| Character limit | Edge length (px) |
| --: | --: |
| <1 | 0 |
| <11 | 147 |
| <21 | 175 |
| <36 | 202 |
| <51 | 230 |
| <65 | 258 |
| <85 | 287 |
| <94 | 314 |
| <123 | 342 |


## Size depending on zoom level

### Testcode
Execute the following snipped to generate the test code:
```python
#!/bin/python3

for i in range(1,11):
  print("^XA^LH0,0^BY0,0,0^FO0,0^BQ,2,"+str(i)+"^FDH,XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX^XZ")
```

### Measured size
| Zoom level  | Edge length (px) |
| --: | --: |
| 1 | 37 |
| 2 | 75 |
| 3 | 111 |
| 4 | 147 |
| 5 | 183 |
| 6 | 221 |
| 7 | 258 |
| 8 | 295 |
| 9 | 332 |
| 10 | 370 |


