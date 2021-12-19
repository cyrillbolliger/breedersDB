# Printer

## Model
Currently, we are using a Zebra P4T. If we have to replace it, consider the much cheaper Bixolon
printers. For some of them, there exists a bluetooth dongle, that lets them connect to cell phones.
[novopos.ch](https://www.novopos.ch) is a possible reseller.

For durability reasons it is essential to have a thermal transfer (not a direct thermal) printer.

## Zebra P4T

### Setup
* Driver [download](https://www.zebra.com/us/en/support-downloads/printers/mobile/p4t.html)
* Installation: Use the ZPL driver (not CPCL).
* Configuration: Enable Passthrough mode: Settings > Advanced Settings > Other > Passthrough.
  Use `${` and `}$` as delimiters.

### Troubleshooting
* Remove printer (in new **and old** windows settings).
* Add it again using the Zebra Setup Utility.
* Reconfigure passthrough mode.
