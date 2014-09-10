<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns="http://www.w3.org/1999/xhtml"
                xmlns:git="http://xml.phpdox.net/gitlog"
                xmlns:pdx="http://xml.phpdox.net/src">

    <xsl:import href="../components.xsl" />

    <xsl:output method="xml" indent="yes" encoding="UTF-8" doctype-system="about:legacy-compat" />

    <xsl:template match="/">
        <html lang="en">
            <xsl:call-template name="head" />
            <body>
                <xsl:call-template name="nav" />
            </body>
        </html>
    </xsl:template>

</xsl:stylesheet>
