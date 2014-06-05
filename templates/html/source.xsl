<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:src="http://xml.phpdox.net/token#"
                xmlns="http://www.w3.org/1999/xhtml">

    <xsl:template match="/">
        <html>
            <head>
                <link rel="stylesheet" href="./test.css" />
                <title>PHP Source Highlight</title>
            </head>
            <body>
                <xsl:call-template name="source" />
            </body>
        </html>
    </xsl:template>

    <xsl:template name="source">
        <table class="source">
            <tr>
                <td class="no">
                    <xsl:for-each select="//src:line">
                        <a href="#line{@no}"><xsl:value-of select="@no" /></a>
                    </xsl:for-each>
                </td>
                <td class="line">
                    <pre>
                        <xsl:apply-templates select="//src:line" />
                    </pre>
                </td>
            </tr>
        </table>
    </xsl:template>

    <xsl:template match="src:line[not(*)]">
        <div id="line{@no}"><br/></div>
    </xsl:template>

    <xsl:template match="src:line">
        <div id="line{@no}">
            <xsl:apply-templates select="src:token" />
        </div>
    </xsl:template>

    <xsl:template match="src:token">
        <span class="token {@name}"><xsl:value-of select="." /></span>
    </xsl:template>

</xsl:stylesheet>
