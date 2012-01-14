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

<xsl:template name="file-properties">
  <xsl:param name="properties"/>
  <xsl:param name="fileName"/>
  <xsl:param name="path"></xsl:param>
  <xsl:param name="namespace"></xsl:param>
  <xsl:if test="count($properties) &gt; 0">
    <h3>Properties</h3>
    <xsl:for-each select="$properties">
      <xsl:sort select="@name"/>
      <div class="group property">
        <h4 id="property_{@name}">$<xsl:value-of select="@name"/></h4>
        <xsl:call-template name="prototype-property">
          <xsl:with-param name="property" select="."/>
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

<xsl:template name="prototype-property">
  <xsl:param name="property"/>
  <xsl:param name="path"></xsl:param>
  <xsl:param name="namespace"></xsl:param>
  <xsl:variable name="name">
    <xsl:text>$</xsl:text>
    <xsl:value-of select="@name"/>
  </xsl:variable>
  <xsl:variable name="type">
    <xsl:choose>
      <xsl:when test="$property/pdox:docblock/pdox:var/@type != ''">
        <xsl:value-of select="$property/pdox:docblock/pdox:var/@type"/>
      </xsl:when>
    </xsl:choose>
  </xsl:variable>
  <xsl:variable name="isStatic" select="$property/@static = 'true'"/>
  <xsl:variable name="visibility" select="$property/@visibility"/>
  <div class="prototype property">
    <xsl:if test="$visibility or $isStatic">
      <ul class="properties">
        <li class="keyword"><xsl:value-of select="$visibility" /></li>
        <xsl:if test="$isStatic">
          <li class="keyword">static</li>
        </xsl:if>
      </ul>
    </xsl:if>
    <xsl:call-template name="variable">
      <xsl:with-param name="name" select="$name"/>
      <xsl:with-param name="type" select="$type"/>
      <xsl:with-param name="path" select="$path"/>
      <xsl:with-param name="namespace" select="$namespace"/>
    </xsl:call-template>
  </div>
</xsl:template>

<xsl:template name="file-dynamic-properties">
  <xsl:param name="properties"/>
  <xsl:param name="fileName"/>
  <xsl:param name="path"></xsl:param>
  <xsl:param name="namespace"></xsl:param>
  <xsl:if test="count($properties) &gt; 0">
    <h3>Dynamic properties</h3>
    <xsl:for-each select="$properties">
      <xsl:sort select="substring-after(@value, '$')"/>
      <xsl:variable name="type" select="substring-before(@value, '$')"/>
      <xsl:variable name="typeStripped" select="substring-after(@value, $type)"/>
      <xsl:variable name="name">
        <xsl:choose>
          <xsl:when test="contains($typeStripped, ' ')">
            <xsl:value-of select="substring-before($typeStripped, ' ')"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="$typeStripped"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:variable>
      <xsl:variable name="description" select="substring-after($typeStripped, ' ')"/>
      <div class="group property">
        <h4 id="dynamicProperty_{$name}"><xsl:value-of select="$name"/></h4>
        <div class="prototype property">
          <ul class="properties">
            <li class="keyword">property</li>
            <xsl:if test="local-name() = 'property-read' or @annotation = 'property-read'">
              <li class="keyword">read-only</li>
            </xsl:if>
          </ul>
          <xsl:call-template name="variable">
            <xsl:with-param name="name" select="$name"/>
            <xsl:with-param name="type" select="normalize-space($type)"/>
            <xsl:with-param name="path" select="$path"/>
            <xsl:with-param name="namespace" select="$namespace"/>
          </xsl:call-template>
        </div>
        <xsl:if test="$description != ''">
          <p class="descriptionShort"><xsl:value-of select="$description"/></p>
        </xsl:if>
        <xsl:call-template name="link-jump-to-top"/>
      </div>
    </xsl:for-each>
  </xsl:if>
</xsl:template>

<xsl:template name="file-property-links">
  <xsl:param name="properties"/>
  <xsl:param name="path"></xsl:param>
  <xsl:param name="namespace"></xsl:param>
  <xsl:if test="count($properties) &gt; 0">
    <h3>Properties</h3>
    <ul>
      <xsl:for-each select="$properties">
        <xsl:sort select="@name"/>
        <li>
          <a href="#property_{@name}">$<xsl:value-of select="@name"/></a>
        </li>
      </xsl:for-each>
    </ul>
  </xsl:if>
</xsl:template>

<xsl:template name="file-dynamic-property-links">
  <xsl:param name="properties"/>
  <xsl:param name="path"></xsl:param>
  <xsl:param name="namespace"></xsl:param>
  <xsl:if test="count($properties) &gt; 0">
    <h3>Dynamic properties</h3>
    <ul>
    <xsl:for-each select="$properties">
      <xsl:sort select="substring-after(@value, '$')"/>
      <xsl:variable name="type" select="substring-before(@value, '$')"/>
      <xsl:variable name="typeStripped" select="substring-after(@value, $type)"/>
      <xsl:variable name="name">
        <xsl:choose>
          <xsl:when test="contains($typeStripped, ' ')">
            <xsl:value-of select="substring-before($typeStripped, ' ')"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="$typeStripped"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:variable>
      <li>
        <a href="#dynamicProperty_{$name}"><xsl:value-of select="$name"/></a>
      </li>
    </xsl:for-each>
    </ul>
  </xsl:if>
</xsl:template>

</xsl:stylesheet>