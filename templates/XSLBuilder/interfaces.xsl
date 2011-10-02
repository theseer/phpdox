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

<xsl:import href="prototype.xsl"/>
<xsl:import href="constants.xsl"/>
<xsl:import href="methods.xsl"/>
<xsl:import href="properties.xsl"/>

<xsl:param name="FORCE_USE_PACKAGES" select="false()"/>

<xsl:template name="interface-index">
  <xsl:param name="index" />
  <xsl:param name="interfaceIndex" />
  <xsl:variable name="consoleOutput" select="cxr:console-write('Generating interface index')"/>
  <xsl:variable name="packages" select="cxr:aggregate-packages($index)/*"/>
  <exsl:document
    href="target://interfaces{$OUTPUT_EXTENSION}"
    method="xml"
    encoding="utf-8"
    standalone="yes"
    doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"
    doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"
    indent="yes"
    omit-xml-declaration="yes">
    <html>
      <xsl:call-template name="html-head">
        <xsl:with-param name="title">Interface Index</xsl:with-param>
      </xsl:call-template>
      <body>
        <xsl:call-template name="page-header">
          <xsl:with-param name="title">Interface Index</xsl:with-param>
        </xsl:call-template>
        <div class="navigation">
          <xsl:call-template name="navigation"/>
        </div>
        <div class="pageBody">
          <div class="content">
            <xsl:choose>
              <xsl:when test="$FORCE_USE_PACKAGES or count($interfaceIndex/pdox:namespace) = 0">
                <xsl:call-template name="interface-list">
                  <xsl:with-param name="interfaces" select="$index//pdox:interface[@package = '']"/>
                </xsl:call-template>
                <xsl:for-each select="$packages[@full != '']">
                  <xsl:variable name="packageName" select="@full"/>
                  <xsl:call-template name="interface-list">
                    <xsl:with-param name="interfaces" select="$index//pdox:interface[@package = $packageName]"/>
                    <xsl:with-param name="package" select="$packageName"/>
                  </xsl:call-template>
                </xsl:for-each>
              </xsl:when>
              <xsl:otherwise>
                <xsl:call-template name="interface-list">
                  <xsl:with-param name="interfaces" select="$interfaceIndex/pdox:interface"/>
                </xsl:call-template>
                <xsl:for-each select="$interfaceIndex/pdox:namespace">
                  <xsl:sort select="@name"/>
                  <xsl:call-template name="interface-list">
                    <xsl:with-param name="interfaces" select="pdox:interface"/>
                    <xsl:with-param name="namespace" select="@name"/>
                  </xsl:call-template>
                </xsl:for-each>
              </xsl:otherwise>
            </xsl:choose>
          </div>
        </div>
        <xsl:call-template name="page-footer"/>
      </body>
    </html>
  </exsl:document>
</xsl:template>

<xsl:template name="interface-list">
  <xsl:param name="interfaces"/>
  <xsl:param name="namespace"></xsl:param>
  <xsl:param name="package"></xsl:param>
  <xsl:if test="count($interfaces) &gt; 0">
    <xsl:choose>
      <xsl:when test="$namespace != ''">
        <h2 id="ns/{translate($namespace, '\', '/')}"><xsl:value-of select="$namespace" /></h2>
      </xsl:when>
      <xsl:when test="$package != ''">
        <h2 id="pkg/{translate($package, '\', '/')}"><xsl:value-of select="$package" /></h2>
      </xsl:when>
    </xsl:choose>
    <ul>
      <xsl:for-each select="$interfaces">
        <xsl:sort select="@full"/>
        <li>
          <a href="{cxr:filename-of-interface(./@full)}">
            <xsl:choose>
              <xsl:when test="@name"><xsl:value-of select="@name" /></xsl:when>
              <xsl:otherwise><xsl:value-of select="@full" /></xsl:otherwise>
            </xsl:choose>
          </a>
        </li>
      </xsl:for-each>
    </ul>
  </xsl:if>
</xsl:template>

<xsl:template name="file-interface">
  <xsl:param name="index" />
  <xsl:param name="fileName" />
  <xsl:param name="interfaceName" />
  <xsl:variable name="file" select="cxr:load-document($fileName)/pdox:file"/>
  <xsl:variable name="interface" select="$file//pdox:interface[@full = $interfaceName]"/>
  <xsl:variable name="target" select="concat('target://', cxr:filename-of-interface($interface/@full))"/>
  <xsl:variable name="path" select="cxr:string-repeat('../', cxr:substring-count($interface/@full, '\'))"/>
  <xsl:variable name="namespace" select="$interface/parent::pdox:namespace/@name"/>
  <xsl:variable name="docblock" select="$interface/pdox:docblock"/>
  <exsl:document
    href="{$target}"
    method="xml"
    encoding="utf-8"
    standalone="yes"
    doctype-public="HTML"
    indent="no"
    omit-xml-declaration="yes">
    <html>
      <xsl:call-template name="html-head">
        <xsl:with-param name="title" select="$interface/@full"/>
        <xsl:with-param name="path" select="$path"/>
      </xsl:call-template>
      <body>
        <xsl:call-template name="page-header">
          <xsl:with-param name="title" select="$interface/@full"/>
        </xsl:call-template>
        <div class="navigation">
          <xsl:call-template name="navigation">
            <xsl:with-param name="selected" select="$interface/@full"/>
            <xsl:with-param name="path" select="$path"/>
          </xsl:call-template>
        </div>
        <div class="pageBody">
          <div class="pageNavigation">
            <xsl:call-template name="file-property-links">
              <xsl:with-param name="properties" select="$interface/pdox:member"/>
              <xsl:with-param name="namespace" select="$namespace"/>
              <xsl:with-param name="path" select="$path"/>
            </xsl:call-template>
            <xsl:call-template name="file-method-links">
              <xsl:with-param name="methods" select="$interface/pdox:method|$interface/pdox:constructor"/>
              <xsl:with-param name="namespace" select="$namespace"/>
              <xsl:with-param name="path" select="$path"/>
            </xsl:call-template>
            <xsl:text> </xsl:text>
          </div>
          <div class="content">
            <h2 class="interfaceName">
              <xsl:call-template name="namespace-ariadne">
                <xsl:with-param name="namespace" select="$namespace"/>
                <xsl:with-param name="path" select="$path"/>
              </xsl:call-template>
              <xsl:if test="string($namespace) != ''">
                <xsl:text>\</xsl:text>
              </xsl:if>
              <xsl:value-of select="@name"/>
            </h2>
            <xsl:call-template name="interface-prototype">
              <xsl:with-param name="interface" select="$interface"/>
              <xsl:with-param name="namespace" select="$interface/@namespace"/>
              <xsl:with-param name="path" select="$path"/>
            </xsl:call-template>
            <xsl:if test="$docblock/pdox:description/@compact != ''">
              <p>
                <xsl:value-of select="$docblock/pdox:description/@compact"/>
              </p>
            </xsl:if>
            <xsl:if test="$docblock/pdox:description/node()">
               <p class="descriptionLarge">
                 <xsl:copy-of select="$docblock/pdox:description/node()"/>
               </p>
            </xsl:if>
            <xsl:variable
              name="children"
              select="cxr:inheritance-children-interface($index, string($interface/@full))//pdox:interface"/>
            <xsl:if test="count($children) &gt; 0">
              <h3>Extended by</h3>
              <ul class="extendedBy">
                <xsl:for-each select="$children">
                  <li>
                    <xsl:call-template name="variable-type">
                      <xsl:with-param name="type" select="string(@full)"/>
                      <xsl:with-param name="path" select="string($path)"/>
                    </xsl:call-template>
                  </li>
                </xsl:for-each>
              </ul>
            </xsl:if>
            <xsl:variable
              name="implementations"
              select="cxr:inheritance-implementations($index, string($interface/@full))//pdox:interface"/>
            <xsl:if test="count($implementations) &gt; 0">
              <h3>Implemented by</h3>
              <ul class="implementedBy">
                <xsl:for-each select="$implementations">
                  <li>
                    <xsl:call-template name="variable-type">
                      <xsl:with-param name="type" select="string(@full)"/>
                      <xsl:with-param name="path" select="string($path)"/>
                    </xsl:call-template>
                  </li>
                </xsl:for-each>
              </ul>
            </xsl:if>
            <xsl:call-template name="file-constants">
              <xsl:with-param name="constants" select="$interface/pdox:constant"/>
            </xsl:call-template>
            <xsl:call-template name="file-methods">
              <xsl:with-param name="methods" select="$interface/pdox:method"/>
              <xsl:with-param name="fileName" select="$fileName"/>
              <xsl:with-param name="namespace" select="$namespace"/>
              <xsl:with-param name="path" select="$path"/>
            </xsl:call-template>
          </div>
        </div>
        <xsl:call-template name="page-footer"/>
      </body>
    </html>
  </exsl:document>
</xsl:template>

</xsl:stylesheet>
