#!/bin/bash
#https://ghostscript.com/doc/current/Devices.htm
#https://ghostscript.com/doc/9.20/VectorDevices.htm#PDFWRITE
#gs -r150 -dSAFER -dBATCH -dNOPAUSE -dNOCACHE -sDEVICE=pdfwrite -sColorConversionStrategy=CMYK -dProcessColorModel=/DeviceCMYK -sOutputFile=print_cmyk.pdf print.pdf
#gs -r150 -dSAFER -dBATCH -dNOPAUSE -dNOCACHE -sDEVICE=pdfwrite -sColorConversionStrategy=CMYK -dProcessColorModel=/DeviceCMYK -sOutputFile=print_cmyk.pdf print.pdf
#gs -r300 -dSAFER -dBATCH -dNOPAUSE -dNOCACHE -sDEVICE=pdfwrite -sColorConversionStrategy=CMYK -UseDeviceIndependentColor -sOutputFile=print_cmyk.pdf print.pdf
gs -r300 -dSAFER -dBATCH -dNOPAUSE -dNOCACHE -dDOCIEDEBUG -dCOLORSCREEN \
	-sDEVICE=pdfwrite \
	-sColorConversionStrategy=CMYK \
	-sProcessColorModel=DeviceCMYK \
	-sOutputFile=print_cmyk.pdf print.pdf
	
#gs -r600 -dNOPAUSE -dBATCH -sDEVICE=png16m -sOutputFile="res_%d.png" print.pdf
#gs -r600 -dNOPAUSE -dBATCH -sDEVICE=tiffg3 -sOutputFile=__print.tiff print.pdf
#gs -r300 -dNOPAUSE -dBATCH -sDEVICE=tiffg3 -sOutputFile=__print.tiff print.pdf
#gs -r600 -dNOPAUSE -dBATCH -sDEVICE=tiffgray -sOutputFile=__print.tiff print.pdf
#gs -r150 -dNOPAUSE -dBATCH -sDEVICE=tiffsep1 -sOutputFile=__print.tiff print_cmyk.pdf
#gs -r300 -dNOPAUSE -dBATCH -sDEVICE=tiffsep -sOutputFile=__print.tiff print_cmyk.pdf
#gs -r96 -dNOPAUSE -dBATCH -sDEVICE=tiffsep -sOutputFile=__print.tiff print_cmyk.pdf

#gs -r300 -dNOPAUSE -dBATCH -sDEVICE=tiffsep -sOutputFile=__print.tiff print.pdf
gs -r300 -dNOPAUSE -dBATCH -sDEVICE=tiffsep -sOutputFile=__print.tiff print_cmyk.pdf

#gs -r96 -dNOPAUSE -dBATCH -sDEVICE=tiffsep -sOutputFile=__print.tiff print.pdf
#gs -r600 -dNOPAUSE -dBATCH -sDEVICE=tiff64nc -sOutputFile=print.tiff print.pdf
#gs -r600 -dNOPAUSE -dBATCH -sDEVICE=tiffsep -sOutputFile=print.tiff print.pdf
#gs -r600 -dNOPAUSE -dBATCH -sDEVICE=tiffsep -sOutputFile=print.tiff print.pdf
#gs -r600 -dNOPAUSE -dBATCH -sDEVICE=pdfimage8 -sOutputFile=print.pdf print.png

