<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns="http://www.w3.org/1999/xhtml" xmlns:file="http://xml.phpdox.de/src#"
    exclude-result-prefixes="#default file">
    
    <xsl:import href="topbar.xsl" />

    <xsl:output method="xml" indent="yes" encoding="utf-8" doctype-public="html" />

    <xsl:variable name="project" select="phe:getProjectNode()"/>

    <xsl:template match="/">
        <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
            <head>
                <meta charset="UTF-8" />
                <link rel="stylesheet" href="css/bootstrap.min.css" />
                <title><xsl:value-of select="$project/@name" /> - API Documentation</title>
                <style type="text/css">
                    body {
                        padding-top: 60px;
                    }
                </style>                
            </head>
            <body>

                <xsl:call-template name="topbar">
                    <xsl:with-param name="active" select="'index'" />
                </xsl:call-template>

                <div class="container">
                
                    <div class="hero-unit">
                        <h1><xsl:value-of select="$project/@name" /></h1>
                        <p>Welcome to the API documentation page. Please select one of the listed classes,
                           interfaces or traits to learn more about the indivdual item. You can navigate back
                           to this page by use of the top navigation bar.<br/>
                        </p>
                        <p>
                            <a href="http://phpdox.de" class="btn primary large">Learn more</a>
                        </p>
                        <p class="muted">The project did not specify a fancy introduction text so this was the boring default text.</p>
                    </div>
                    
                    <div class="row">
                        <div class="span6">
                            <h2>Classes</h2>
                            <p style="margin-left:1em">
                                <xsl:copy-of select="phe:getClassList()" />
                            </p>
                        </div>
                        <div class="span5">
                            <h2>Interfaces</h2>
                            <p style="margin-left:1em">
                                <xsl:copy-of select="phe:getInterfaceList()" />
                            </p>
                        </div>
                        
                        <div class="span5">
                            <h2>Traits</h2>
                            <p class="muted" style="padding-left:1em">No traits defined</p>
                        </div>
                    </div>
                        
                    <footer>
                        <p>Generated using phpDox <xsl:value-of select="phe:version()" /></p>
                    </footer>
                </div>
            </body>
        </html>
    </xsl:template>

</xsl:stylesheet>