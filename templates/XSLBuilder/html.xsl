<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns="http://www.w3.org/1999/xhtml/"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:pdox="http://xml.phpdox.de/src#"
  xmlns:cxr="http://thomas.weinert.info/carica/xr"
  exclude-result-prefixes="#default pdox cxr">

<xsl:template name="html-head">
  <xsl:param name="title"></xsl:param>
  <xsl:param name="path"></xsl:param>
  <head>
    <title>
      <xsl:choose>
        <xsl:when test="/project/title and protect/title != ''">
          <xsl:copy-of select="/project/title/node()"/>
        </xsl:when>
        <xsl:otherwise>
          Documentation
        </xsl:otherwise>
      </xsl:choose>
      <xsl:if test="$title != ''">
        <xsl:text> - </xsl:text>
        <xsl:value-of select="$title"/>
      </xsl:if>
    </title>
    <link rel="stylesheet" type="text/css" href="{$path}files/style.css"/>
  </head>
</xsl:template>

<xsl:template name="page-header">
  <xsl:param name="title"></xsl:param>
  <div class="pageHeader">
    <h1 id="top">
      <xsl:choose>
        <xsl:when test="/project/title and protect/title != ''">
          <xsl:copy-of select="/project/title/node()"/>
        </xsl:when>
        <xsl:otherwise>
          Documentation
        </xsl:otherwise>
      </xsl:choose>
      <xsl:if test="$title != ''">
        <xsl:text> - </xsl:text>
        <xsl:value-of select="$title"/>
      </xsl:if>
    </h1>
  </div>
</xsl:template>

<xsl:template name="page-footer">
  <div class="pageFooter">
    <xsl:text> </xsl:text>
  </div>
</xsl:template>

<xsl:template name="link-jump-to-top">
  <a href="#top" class="jumpToTop">Jump To Top</a>
</xsl:template>

</xsl:stylesheet>