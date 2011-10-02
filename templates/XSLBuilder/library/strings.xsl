<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
  version="1.0" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
  xmlns:func="http://exslt.org/functions"
  xmlns:cxr="http://thomas.weinert.info/carica/xr"
  extension-element-prefixes="func"
  exclude-result-prefixes="cxr">

<func:function name="cxr:substring-count">
  <xsl:param name="string"/>
  <xsl:param name="substring"/>
  <xsl:param name="counter" select="0"/>
  <xsl:variable name="result">
    <xsl:choose>
      <xsl:when test="contains($string, $substring)">
        <xsl:value-of select="cxr:substring-count(substring-after($string, $substring), $substring, $counter + 1)" />
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="$counter" />
      </xsl:otherwise>
    </xsl:choose>
  </xsl:variable>
  <func:result select="$result"/>
</func:function>

<func:function name="cxr:string-repeat">
  <xsl:param name="string"/>
  <xsl:param name="counter"/>
  <xsl:variable name="result">
    <xsl:if test="$counter &gt; 0">
      <xsl:value-of select="cxr:string-repeat($string, $counter - 1)" />
    </xsl:if>
    <xsl:value-of select="$string" />
  </xsl:variable>
  <func:result select="$result"/>
</func:function>
  
</xsl:stylesheet>