<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:src="http://xml.phpdox.de/src#"
                exclude-result-prefixes="src">


    <xsl:param name="method" select="''" />
    <xsl:template match="/">
        <method>
            <xsl:copy-of select="//src:method[@name= $method] | //src:constructor[@name = $method] | //src:destructor[@name = $method]" />
        </method>
    </xsl:template>

</xsl:stylesheet>