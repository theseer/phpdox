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

<xsl:template name="file-methods">
  <xsl:param name="methods"/>
  <xsl:param name="fileName"/>
  <xsl:param name="path"></xsl:param>
  <xsl:param name="namespace"></xsl:param>
  <xsl:if test="count($methods) &gt; 0">
    <h3>Methods</h3>
    <xsl:for-each select="$methods">
      <xsl:sort select="@name"/>
      <div class="group method">
        <h4 id="method_{@name}"><xsl:value-of select="@name"/></h4>
        <xsl:call-template name="function-prototype">
          <xsl:with-param name="function" select="."/>
          <xsl:with-param name="file" select="$fileName"/>
          <xsl:with-param name="path" select="$path"/>
          <xsl:with-param name="namespace" select="$namespace"/>
        </xsl:call-template>
        <xsl:if test="pdox:docblock/pdox:description/@compact != ''">
          <p class="descriptionShort"><xsl:value-of select="pdox:docblock/pdox:description/@compact"/></p>
        </xsl:if>
        <xsl:if test="pdox:docblock/pdox:description/node()">
          <p class="descriptionLarge">
            <xsl:copy-of select="pdox:docblock/pdox:description/node()"/>
          </p>
        </xsl:if>
        <xsl:call-template name="link-jump-to-top"/>
      </div>
    </xsl:for-each>
  </xsl:if>
</xsl:template>

<xsl:template name="file-dynamic-methods">
  <xsl:param name="methods"/>
  <xsl:param name="fileName"/>
  <xsl:param name="path"></xsl:param>
  <xsl:param name="namespace"></xsl:param>
  <xsl:if test="count($methods) &gt; 0">
    <h3>Dynamic Methods</h3>
    <xsl:for-each select="$methods">
      <xsl:sort select="substring-after(@value, ' ')"/>
      <xsl:variable name="returnType" select="substring-before(@value, ' ')"/>
      <xsl:variable name="returnStripped" select="substring-after(@value, $returnType)"/>
      <xsl:variable name="name" select="normalize-space(substring-before($returnStripped, '('))"/>
      <xsl:variable name="description" select="substring-after($returnStripped, ')')"/>
      <div class="group method">
        <h4 id="dynamicMethod_{$name}"><xsl:value-of select="$name"/></h4>
        <div class="prototype function">
          <ul class="properties">
            <li class="keyword">function</li>
          </ul>
          <xsl:call-template name="variable-type-list">
            <xsl:with-param name="type" select="normalize-space($returnType)"/>
            <xsl:with-param name="path" select="$path"/>
            <xsl:with-param name="namespace" select="$namespace"/>
          </xsl:call-template>
          <span class="name"><xsl:value-of select="$name"/>()</span>
        </div>
        <xsl:if test="$description != ''">
          <p class="descriptionShort"><xsl:value-of select="$description"/></p>
        </xsl:if>
        <xsl:call-template name="link-jump-to-top"/>
      </div>
    </xsl:for-each>
  </xsl:if>
</xsl:template>

<xsl:template name="file-method-links">
  <xsl:param name="methods"/>
  <xsl:param name="path"></xsl:param>
  <xsl:param name="namespace"></xsl:param>
  <xsl:if test="count($methods) &gt; 0">
    <h3>Methods</h3>
    <ul>
      <xsl:for-each select="$methods">
        <xsl:sort select="@name"/>
        <li>
          <a href="#method_{@name}"><xsl:value-of select="@name"></xsl:value-of></a>
        </li>
      </xsl:for-each>
    </ul>
  </xsl:if>
</xsl:template>

<xsl:template name="file-dynamic-method-links">
  <xsl:param name="methods"/>
  <xsl:param name="path"></xsl:param>
  <xsl:param name="namespace"></xsl:param>
  <xsl:if test="count($methods) &gt; 0">
    <h3>Dynamic Methods</h3>
    <ul>
      <xsl:for-each select="$methods">
        <xsl:sort select="substring-after(@value, ' ')"/>
        <xsl:variable name="returnType" select="substring-before(@value, ' ')"/>
        <xsl:variable name="returnStripped" select="substring-after(@value, $returnType)"/>
        <xsl:variable name="name" select="normalize-space(substring-before($returnStripped, '('))"/>
        <li>
          <a href="#dynamicMethod_{$name}"><xsl:value-of select="$name"></xsl:value-of></a>
        </li>
      </xsl:for-each>
    </ul>
  </xsl:if>
</xsl:template>

</xsl:stylesheet>