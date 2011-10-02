<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns="http://www.w3.org/1999/xhtml/"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:pdox="http://xml.phpdox.de/src#"
  xmlns:cxr="http://thomas.weinert.info/carica/xr"
  exclude-result-prefixes="#default pdox cxr">

<xsl:template name="variable">
  <xsl:param name="name"/>
  <xsl:param name="type"/>
  <xsl:param name="path"></xsl:param>
  <xsl:param name="namespace"></xsl:param>
  <span class="variable">
    <xsl:call-template name="variable-type-list">
      <xsl:with-param name="type" select="$type"/>
      <xsl:with-param name="path" select="$path"/>
      <xsl:with-param name="namespace" select="$namespace"/>
    </xsl:call-template>
    <span class="name"><xsl:value-of select="$name"/></span>
  </span>
</xsl:template>

<xsl:template name="variable-type-list">
  <xsl:param name="type"/>
  <xsl:param name="path"></xsl:param>
  <xsl:param name="namespace"></xsl:param>
  <xsl:if test="$type != '{unknown}'">
    <xsl:variable name="parts" select="cxr:parse-type-string($type)/*/*"/>
    <ul class="types">
      <xsl:for-each select="$parts">
        <xsl:variable name="text" select="string(text())"/>
        <li>
          <xsl:choose>
            <xsl:when test="local-name(.) = 'type'">
              <xsl:attribute name="class">type</xsl:attribute>
              <xsl:call-template name="variable-type">
                <xsl:with-param name="type" select="$text"/>
                <xsl:with-param name="path" select="$path"/>
                <xsl:with-param name="namespace" select="$namespace"/>
              </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
              <xsl:attribute name="class">operator</xsl:attribute>
              <xsl:value-of select="$text"/>
            </xsl:otherwise>
          </xsl:choose>
        </li>
      </xsl:for-each>
    </ul>
  </xsl:if>
</xsl:template>

<xsl:template name="variable-type">
  <xsl:param name="type"/>
  <xsl:param name="path"></xsl:param>
  <xsl:param name="namespace"></xsl:param>
  <xsl:variable name="href" select="cxr:filename-of-type($type, $path, $namespace)"/>
  <xsl:choose>
    <xsl:when test="$href and $href != ''">
      <a href="{$href}"><xsl:value-of select="$type"/></a>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="$type"/>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

</xsl:stylesheet>