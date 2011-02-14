<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
   xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
   xmlns="http://www.w3.org/1999/xhtml">
   
   <xsl:output method="xml" indent="yes" encoding="utf-8"/>
   
   <xsl:template match="/">
   	  <html>
   	  	<head>
   	  	</head>
   	  	<body>   	  	
   	  		<xsl:copy-of select="/" />
   	  	</body>
   	  </html>
   </xsl:template>
   
</xsl:stylesheet>
