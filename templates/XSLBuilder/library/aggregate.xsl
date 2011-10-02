<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:exsl="http://exslt.org/common"
  xmlns:func="http://exslt.org/functions"
  xmlns:cxr="http://thomas.weinert.info/carica/xr"
  xmlns:pdox="http://xml.phpdox.de/src#"
  extension-element-prefixes="exsl func"
  exclude-result-prefixes="cxr pdox">

<func:function name="cxr:aggregate">
  <xsl:param name="classes"/>
  <xsl:param name="interfaces"/>
  <xsl:variable name="consoleLog" select="cxr:console-write('Aggregating data')"/>
  <xsl:variable
    name="consoleProgress"
    select="cxr:console-progress(true(), count($classes//pdox:class) + count($interfaces//pdox:interface))"/>
  <xsl:variable name="result">
    <pdox:structure>
      <xsl:for-each select="$classes//pdox:class">
        <xsl:variable name="consoleProgressStep" select="cxr:console-progress()"/>
        <xsl:call-template name="aggregate-class">
          <xsl:with-param name="fileName" select="concat('source://', @xml)"/>
          <xsl:with-param name="className" select="@full"/>
        </xsl:call-template>
      </xsl:for-each>
      <xsl:for-each select="$interfaces//pdox:interface">
        <xsl:variable name="consoleProgressStep" select="cxr:console-progress()"/>
        <xsl:call-template name="aggregate-interface">
          <xsl:with-param name="fileName" select="concat('source://', @xml)"/>
          <xsl:with-param name="interfaceName" select="@full"/>
        </xsl:call-template>
      </xsl:for-each>
    </pdox:structure>
  </xsl:variable>
  <xsl:variable name="consoleLogEnd" select="cxr:console-write('&#10;')"/>
  <func:result select="exsl:node-set($result)"/>
</func:function>

<xsl:template name="aggregate-class">
  <xsl:param name="fileName"/>
  <xsl:param name="className"/>
  <xsl:variable name="file" select="cxr:load-document($fileName)"/>
  <xsl:variable name="class" select="$file//pdox:class[@full = $className]"/>
  <xsl:variable name="package">
    <xsl:choose>
      <xsl:when test="$class/pdox:docblock/pdox:package/@value != ''">
        <xsl:value-of select="$class/pdox:docblock/pdox:package/@value"/>
        <xsl:if test="$class/pdox:docblock/pdox:subpackage/@value != ''">
          <xsl:text>\</xsl:text>
          <xsl:value-of select="$class/pdox:docblock/pdox:subpackage/@value"/>
        </xsl:if>
      </xsl:when>
      <xsl:when test="$class/parent::pdox:namespace">
        <xsl:value-of select="$class/parent::pdox:namespace/@name"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:text></xsl:text>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>
  <pdox:class file="{$fileName}" full="{$class/@full}" package="{$package}">
    <xsl:for-each select="$class/pdox:extends">
      <pdox:extends full="{@full}"/>
    </xsl:for-each>
    <xsl:for-each select="$class/pdox:implements">
      <pdox:implements full="{@full}"/>
    </xsl:for-each>
  </pdox:class>
</xsl:template>

<xsl:template name="aggregate-interface">
  <xsl:param name="fileName"/>
  <xsl:param name="interfaceName"/>
  <xsl:variable name="file" select="cxr:load-document($fileName)"/>
  <xsl:variable name="interface" select="$file//pdox:interface[@full = $interfaceName]"/>
  <xsl:variable name="package">
    <xsl:choose>
      <xsl:when test="$interface/pdox:docblock/pdox:package/@value != ''">
        <xsl:value-of select="$interface/pdox:docblock/pdox:package/@value"/>
        <xsl:if test="$interface/pdox:docblock/pdox:subpackage/@value != ''">
          <xsl:text>\</xsl:text>
          <xsl:value-of select="$interface/pdox:docblock/pdox:subpackage/@value"/>
        </xsl:if>
      </xsl:when>
      <xsl:when test="$interface/parent::pdox:namespace">
        <xsl:value-of select="$interface/parent::pdox:namespace/@name"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:text></xsl:text>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>
  <pdox:interface full="{$interface/@full}" package="{$package}">
    <xsl:for-each select="$interface/pdox:extends">
      <pdox:extends full="{@full}"/>
    </xsl:for-each>
  </pdox:interface>
</xsl:template>

<func:function name="cxr:inheritance-superclasses">
  <xsl:param name="index"/>
  <xsl:param name="className"/>
  <xsl:variable name="result">
    <pdox:parents>
      <xsl:call-template name="inheritance-fetch-parents">
        <xsl:with-param name="index" select="$index"/>
        <xsl:with-param name="className" select="string($className)"/>
      </xsl:call-template>
    </pdox:parents>
  </xsl:variable>
  <func:result select="exsl:node-set($result)"/>
</func:function>

<xsl:template name="inheritance-fetch-parents">
  <xsl:param name="index"/>
  <xsl:param name="className"/>
  <xsl:variable name="parent" select="$index//pdox:class[@full = $className]/pdox:extends"/>
  <xsl:if test="$parent">
    <xsl:call-template name="inheritance-fetch-parents">
      <xsl:with-param name="index" select="$index"/>
      <xsl:with-param name="className" select="string($parent/@full)"/>
    </xsl:call-template>
  </xsl:if>
  <pdox:class full="{$className}"/>
</xsl:template>

<func:function name="cxr:inheritance-children-class">
  <xsl:param name="index"/>
  <xsl:param name="name"/>
  <xsl:variable name="result">
    <pdox:extended-by>
      <xsl:for-each select="$index//pdox:class[pdox:extends[@full = $name]]">
        <pdox:class full="{@full}"/>
      </xsl:for-each>
    </pdox:extended-by>
  </xsl:variable>
  <func:result select="exsl:node-set($result)"/>
</func:function>

<func:function name="cxr:inheritance-children-interface">
  <xsl:param name="index"/>
  <xsl:param name="name"/>
  <xsl:variable name="result">
    <pdox:extended-by>
      <xsl:for-each select="$index//pdox:interface[pdox:extends[@full = $name]]">
        <pdox:interface full="{@full}"/>
      </xsl:for-each>
    </pdox:extended-by>
  </xsl:variable>
  <func:result select="exsl:node-set($result)"/>
</func:function>

<func:function name="cxr:inheritance-implementations">
  <xsl:param name="index"/>
  <xsl:param name="name"/>
  <xsl:variable name="result">
    <pdox:implemented-by>
      <xsl:for-each select="$index//pdox:class[pdox:implements[@full = $name]]">
        <pdox:interface full="{@full}"/>
      </xsl:for-each>
    </pdox:implemented-by>
  </xsl:variable>
  <func:result select="exsl:node-set($result)"/>
</func:function>

<func:function name="cxr:aggregate-packages">
  <xsl:param name="index"/>
  <xsl:variable name="all">
    <xsl:for-each select="$index//pdox:class|$index//pdox:interface">
      <xsl:sort select="@package"/>
      <pdox:package full="{@package}"/>
    </xsl:for-each>
  </xsl:variable>
  <xsl:variable name="list" select="exsl:node-set($all)/*"/>
  <xsl:variable name="result">
    <xsl:for-each select="$list">
      <xsl:variable name="offset" select="position()"/>
      <xsl:variable name="previous" select="$list[position() = $offset - 1]"/>
      <xsl:if test="not($previous) or (./@full != $previous/@full)">
        <pdox:package full="{@full}"/>
      </xsl:if>
    </xsl:for-each>
  </xsl:variable>
  <func:result select="exsl:node-set($result)"/>
</func:function>

</xsl:stylesheet>