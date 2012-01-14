<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns="http://www.w3.org/1999/xhtml/"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:pdox="http://xml.phpdox.de/src#"
  xmlns:cxr="http://thomas.weinert.info/carica/xr"
  exclude-result-prefixes="#default pdox cxr">

<xsl:template name="navigation">
  <xsl:param name="path"></xsl:param>
  <ul>
    <li>
      <a href="{$path}index{$OUTPUT_EXTENSION}">Start</a>
    </li>
    <li>
      <a href="{$path}classes{$OUTPUT_EXTENSION}">Classes</a>
    </li>
    <xsl:if test="count($INTERFACES//pdox:interface) &gt; 0">
      <li>
        <a href="{$path}interfaces{$OUTPUT_EXTENSION}">Interfaces</a>
      </li>
    </xsl:if>
  </ul>
</xsl:template>

<xsl:template name="namespace-ariadne">
  <xsl:param name="namespace"/>
  <xsl:param name="path"></xsl:param>
  <xsl:call-template name="namespace-ariadne-loop">
    <xsl:with-param name="stack" select="$namespace"/>
    <xsl:with-param name="path" select="$path"/>
  </xsl:call-template>
</xsl:template>

<xsl:template name="namespace-ariadne-loop">
  <xsl:param name="stack"/>
  <xsl:param name="prefix"></xsl:param>
  <xsl:param name="path"></xsl:param>

  <xsl:variable name="caption">
    <xsl:choose>
      <xsl:when test="contains($stack, '\')"><xsl:value-of select="substring-before($stack, '\')"/></xsl:when>
      <xsl:otherwise><xsl:value-of select="$stack"/></xsl:otherwise>
    </xsl:choose>
  </xsl:variable>
  <xsl:variable name="namespace" select="concat($prefix, $caption)"/>

  <xsl:if test="$prefix != ''">
    <xsl:text>\</xsl:text>
  </xsl:if>
  <xsl:choose>
    <xsl:when test="$NAMESPACES[@name = $namespace]">
      <a href="{cxr:filename-of-namespace($namespace, $path)}"><xsl:value-of select="$caption"/></a>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="$caption"/>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:if test="contains($stack, '\')">
    <xsl:call-template name="namespace-ariadne-loop">
      <xsl:with-param name="stack" select="substring-after($stack, '\')"/>
      <xsl:with-param name="prefix" select="concat($prefix, $caption, '\')"/>
      <xsl:with-param name="path" select="$path"/>
    </xsl:call-template>
  </xsl:if>
</xsl:template>

</xsl:stylesheet>