<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns="http://www.w3.org/1999/xhtml" xmlns:file="http://xml.phpdox.de/src#"
    xmlns:cfg="http://phpdox.de/config"
    xmlns:html="http://www.w3.org/1999/xhtml"
    exclude-result-prefixes="#default file">
    
    <xsl:import href="topbar.xsl" />

    <xsl:output method="xml" indent="yes" encoding="utf-8" />

    <xsl:variable name="project" select="phe:getProjectNode()"/>

    <xsl:template match="/">
        <html class="no-js" lang="en">
            <head>
                <meta charset="utf-8" />
                <title><xsl:value-of select="$project/@name" /> - API Documentation</title>
                <link href="css/normalize.css" rel="stylesheet" type="text/css" media="all" />
                <link href="css/styles.css" rel="stylesheet" type="text/css" media="all" />
                <!-- next comment for IE-performance -->
                <!--[if lte IE 8]><![endif]-->
                <!--[if lte IE 8]><link href="css/oldie.css" rel="stylesheet" type="text/css" media="all" /><![endif]-->
            </head>

            <body>

                <div class="wrapper clearfix">
                    <div class="topbar clearfix">
                        <h1><a class="brand" href="./index.html"><xsl:value-of select="$project/@name" /> - API Documentation</a></h1>
                    </div>

                    <div class="indexcontent">
                        <h2><xsl:value-of select="$project/@name" /></h2>
                        <p>Welcome to the API documentation page. Please select one of the listed classes, interfaces or traits to learn more about the indivdual item. You can navigate back to this page by use of the top navigation bar.</p>

                        <xsl:copy-of select="phe:getClassList()/*" />
                        <xsl:copy-of select="phe:getInterfaceList()/*" />
                        <xsl:copy-of select="phe:getTraitList()/*" />

                        <div class="footer"><xsl:value-of select="phe:info()" /></div>
                    </div>
                </div>
            </body>
        </html>
    </xsl:template>

</xsl:stylesheet>