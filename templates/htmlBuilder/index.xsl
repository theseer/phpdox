<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
    xmlns="http://www.w3.org/1999/xhtml"
    xmlns:file="http://xml.phpdox.de/src#"
    exclude-result-prefixes="#default file">
   
    <xsl:output method="xml" indent="yes" encoding="utf-8" />

    <xsl:template match="/">
    <html xmlns="http://www.w3.org/1999/xhtml">
        <head>
            <link type="text/css" href="css/style.css" rel="stylesheet" />
        </head>
        <body>
            <div id="head"></div>
            <h1 class="project">Project title here</h1>
            <div class="content">
                <xsl:copy-of select="hb:getClassList()" />
            </div>    
        </body>
    </html>
   </xsl:template>

</xsl:stylesheet>
