<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
  version="1.0"
  xmlns="http://www.w3.org/1999/xhtml/"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:exsl="http://exslt.org/common"
  xmlns:pdox="http://xml.phpdox.de/src#"
  xmlns:cxr="http://thomas.weinert.info/carica/xr"
  extension-element-prefixes="exsl"
  exclude-result-prefixes="#default pdox cxr">

<xsl:template name="file-constants">
  <xsl:param name="constants"/>
  <xsl:param name="fileName"/>
  <xsl:param name="path"></xsl:param>
  <xsl:param name="namespace"></xsl:param>
  <xsl:if test="count($constants) &gt; 0">
    <h3>Constants</h3>
    <div class="group constant">
      <xsl:for-each select="$constants">
        <xsl:sort select="@name"/>
          <h4 id="constant_{@name}"><xsl:value-of select="@name"/></h4>
          <xsl:call-template name="prototype-constant">
            <xsl:with-param name="property" select="."/>
            <xsl:with-param name="path" select="$path"/>
            <xsl:with-param name="namespace" select="$namespace"/>
          </xsl:call-template>
      </xsl:for-each>
    </div>
    <xsl:call-template name="link-jump-to-top"/>
  </xsl:if>
</xsl:template>

<xsl:template name="prototype-constant">
  <xsl:param name="constant"/>
  <xsl:param name="path"></xsl:param>
  <xsl:param name="namespace"></xsl:param>
  <div class="prototype property">
    <span class="keyword">const </span>
    <span class="name"><xsl:value-of select="@name"/></span>
    <span class="operator"><xsl:text> = </xsl:text></span>
    <span class="value"><xsl:value-of select="@value"/></span>
  </div>
</xsl:template>

</xsl:stylesheet>