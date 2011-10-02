<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns="http://www.w3.org/1999/xhtml/"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:pdox="http://xml.phpdox.de/src#"
  xmlns:cxr="http://thomas.weinert.info/carica/xr"
  exclude-result-prefixes="#default pdox cxr">

<xsl:import href="variable.xsl"/>

<xsl:template name="class-prototype">
  <xsl:param name="class" />
  <xsl:param name="path"></xsl:param>
  <xsl:param name="namespace"></xsl:param>
  <div class="prototype class">
    <span class="keyword">class</span>
    <span class="type"><xsl:value-of select="$class/@name"/></span>
    <xsl:if test="$class/pdox:extends">
      <ul class="inheritance extends">
        <li class="keyword">extends</li>
        <li class="type">
          <xsl:call-template name="variable-type">
            <xsl:with-param name="type" select="string($class/pdox:extends/@full)"/>
            <xsl:with-param name="path" select="string($path)"/>
            <xsl:with-param name="namespace" select="string($namespace)"/>
          </xsl:call-template>
        </li>
      </ul>
    </xsl:if>
    <xsl:if test="$class/pdox:implements">
      <ul class="inheritance implements">
        <li class="keyword">implements</li>
        <xsl:for-each select="$class/pdox:implements">
          <xsl:if test="position() > 1">
            <li class="operator"><xsl:text>, </xsl:text></li>
          </xsl:if>
          <li class="type">
            <xsl:call-template name="variable-type">
              <xsl:with-param name="type" select="string(@full)"/>
              <xsl:with-param name="path" select="string($path)"/>
              <xsl:with-param name="namespace" select="string($namespace)"/>
            </xsl:call-template>
          </li>
        </xsl:for-each>
      </ul>
    </xsl:if>
  </div>
</xsl:template>

<xsl:template name="interface-prototype">
  <xsl:param name="interface" />
  <xsl:param name="path"></xsl:param>
  <xsl:param name="namespace"></xsl:param>
  <div class="prototype interface">
    <span class="keyword">interface</span>
    <span class="type"><xsl:value-of select="$interface/@name"/></span>
    <xsl:if test="$interface/pdox:extends">
      <ul class="inheritance extends">
        <li class="keyword">extends</li>
        <li class="type">
          <xsl:call-template name="variable-type">
            <xsl:with-param name="type" select="string($interface/pdox:extends/@full)"/>
            <xsl:with-param name="path" select="string($path)"/>
            <xsl:with-param name="namespace" select="string($namespace)"/>
          </xsl:call-template>
        </li>
      </ul>
    </xsl:if>
  </div>
</xsl:template>

<xsl:template name="function-prototype">
  <xsl:param name="function" />
  <xsl:param name="path"></xsl:param>
  <xsl:param name="namespace"></xsl:param>
  <xsl:param name="name" select="$function/@name"/>
  <!--  position -->
  <xsl:param name="file"></xsl:param>
  <xsl:param name="lineNumber" select="$function/@start"/>
  <!-- function properties -->
  <xsl:param name="visibility" select="$function/@visibility"/>
  <xsl:param name="isAbstract" select="$function/@abstract = 'true'"/>
  <xsl:param name="isFinal" select="$function/@final = 'true'"/>
  <xsl:param name="isStatic" select="$function/@static = 'true'"/>
  <!-- parameter data -->
  <xsl:param name="parameters" select="$function/pdox:parameter"/>
  <xsl:param name="parameterDocs" select="$function/pdox:docblock/pdox:param"/>
  <xsl:param name="return" select="$function/pdox:docblock/pdox:return"/>

  <div class="prototype function">
    <xsl:if test="$visibility or $isAbstract or $isFinal or $isStatic">
      <ul class="properties">
        <li class="keyword"><xsl:value-of select="$visibility" /></li>
        <xsl:if test="$isAbstract">
          <li class="keyword">abstract</li>
        </xsl:if>
        <xsl:if test="$isFinal">
          <li class="keyword">final</li>
        </xsl:if>
        <xsl:if test="$isStatic">
          <li class="keyword">static</li>
        </xsl:if>
      </ul>
    </xsl:if>
    <xsl:if test="$return and $return/@type != ''">
      <xsl:call-template name="variable-type-list">
        <xsl:with-param name="type" select="$return/@type"/>
        <xsl:with-param name="path" select="$path"/>
        <xsl:with-param name="namespace" select="string($namespace)"/>
      </xsl:call-template>
    </xsl:if>
    <span class="name">
      <xsl:text> </xsl:text>
      <xsl:if test="@byreference = 'true'">
        <xsl:text>&amp;</xsl:text>
      </xsl:if>
      <xsl:value-of select="@name" />
    </span>
    <xsl:call-template name="function-parameters">
      <xsl:with-param name="parameters" select="$parameters"/>
      <xsl:with-param name="documentation" select="$parameterDocs"/>
      <xsl:with-param name="path" select="$path"/>
      <xsl:with-param name="namespace" select="string($namespace)"/>
    </xsl:call-template>
  </div>
</xsl:template>

<xsl:template name="function-parameters">
  <xsl:param name="parameters"/>
  <xsl:param name="documentation"/>
  <xsl:param name="path"></xsl:param>
  <xsl:param name="namespace"></xsl:param>
  <ul class="parameters">
    <li class="operator"><xsl:text>( </xsl:text></li>
    <xsl:if test="$parameters and count($parameters) > 0">
      <xsl:for-each select="$parameters">
        <xsl:variable name="name">
          <xsl:if test="@byreference = 'true'">
            <xsl:text>&amp;</xsl:text>
          </xsl:if>
          <xsl:text>$</xsl:text>
          <xsl:value-of select="@name"/>
        </xsl:variable>
        <xsl:if test="@optional = 'true'">
          <li class="operator"><xsl:text>[</xsl:text></li>
        </xsl:if>
        <xsl:call-template name="function-parameter">
          <xsl:with-param name="parameter" select="."/>
          <xsl:with-param name="parameterName" select="$name"/>
          <xsl:with-param name="documentation" select="$documentation[@variable = $name]"/>
          <xsl:with-param name="path" select="$path"/>
          <xsl:with-param name="namespace" select="$namespace"/>
        </xsl:call-template>
        <xsl:choose>
          <xsl:when test="position() = last()">
            <xsl:for-each select="$parameters[@optional = 'true']">
              <li class="operator"><xsl:text>]</xsl:text></li>
            </xsl:for-each>
          </xsl:when>
          <xsl:otherwise>
            <li class="operator"><xsl:text>, </xsl:text></li>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:for-each>
    </xsl:if>
    <li class="operator"><xsl:text> )</xsl:text></li>
  </ul>
</xsl:template>

<xsl:template name="function-parameter">
  <xsl:param name="parameter"/>
  <xsl:param name="documentation"/>
  <xsl:param name="parameterName" select="$name"/>
  <xsl:param name="path"></xsl:param>
  <xsl:param name="namespace"></xsl:param>
  <xsl:variable name="parameterType">
    <xsl:choose>
      <xsl:when test="$documentation and $documentation/@type != ''">
        <xsl:value-of select="$documentation/@type"/>
      </xsl:when>
      <xsl:when test="$parameter/@type">
        <xsl:value-of select="$parameter/@type"/>
      </xsl:when>
    </xsl:choose>
  </xsl:variable>
  <li class="parameter">
    <xsl:call-template name="variable">
      <xsl:with-param name="name" select="$parameterName"/>
      <xsl:with-param name="type" select="$parameterType"/>
      <xsl:with-param name="path" select="$path"/>
      <xsl:with-param name="namespace" select="$namespace"/>
    </xsl:call-template>
  </li>
</xsl:template>

</xsl:stylesheet>