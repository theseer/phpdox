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

<xsl:template name="class-index">
  <xsl:param name="index" />
  <xsl:param name="classIndex" />
  <xsl:variable name="consoleOutput" select="cxr:console-write('Generating class index')"/>
  <xsl:variable name="packages" select="cxr:aggregate-packages($index)/*"/>
  <exsl:document
    href="target://classes{$OUTPUT_EXTENSION}"
    method="xml"
    encoding="utf-8"
    standalone="yes"
    doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"
    doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"
    indent="yes"
    omit-xml-declaration="yes">
    <html>
      <xsl:call-template name="html-head">
        <xsl:with-param name="title">Class Index</xsl:with-param>
      </xsl:call-template>
      <body>
        <xsl:call-template name="page-header">
          <xsl:with-param name="title">Class Index</xsl:with-param>
        </xsl:call-template>
        <div class="navigation">
          <xsl:call-template name="navigation"/>
        </div>
        <div class="pageBody">
          <div class="content">
            <xsl:choose>
              <xsl:when test="$FORCE_USE_PACKAGES or count($classIndex/pdox:namespace) = 0">
                <xsl:call-template name="class-list">
                  <xsl:with-param name="classes" select="$index//pdox:class[@package = '']"/>
                </xsl:call-template>
                <xsl:for-each select="$packages[@full != '']">
                  <xsl:variable name="packageName" select="@full"/>
                  <xsl:call-template name="class-list">
                    <xsl:with-param name="classes" select="$index//pdox:class[@package = $packageName]"/>
                    <xsl:with-param name="package" select="$packageName"/>
                  </xsl:call-template>
                </xsl:for-each>
              </xsl:when>
              <xsl:otherwise>
                <xsl:call-template name="class-list">
                  <xsl:with-param name="classes" select="$classIndex/pdox:class"/>
                </xsl:call-template>
                <xsl:for-each select="$classIndex/pdox:namespace">
                  <xsl:sort select="@name"/>
                  <xsl:call-template name="class-list">
                    <xsl:with-param name="classes" select="pdox:class"/>
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

<xsl:template name="class-list">
  <xsl:param name="classes"/>
  <xsl:param name="namespace"></xsl:param>
  <xsl:param name="package"></xsl:param>
  <xsl:if test="count($classes) &gt; 0">
    <xsl:choose>
      <xsl:when test="$namespace != ''">
        <h2 id="ns/{translate($namespace, '\', '/')}"><xsl:value-of select="$namespace" /></h2>
      </xsl:when>
      <xsl:when test="$package != ''">
        <h2 id="pkg/{translate($package, '\', '/')}"><xsl:value-of select="$package" /></h2>
      </xsl:when>
    </xsl:choose>
    <ul>
      <xsl:for-each select="$classes">
        <xsl:sort select="@full"/>
        <li>
          <a href="{cxr:filename-of-class(./@full)}">
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

<xsl:template name="file-class">
  <xsl:param name="index" />
  <xsl:param name="fileName" />
  <xsl:param name="className" />
  <xsl:variable name="file" select="cxr:load-document($fileName)/pdox:file"/>
  <xsl:variable name="class" select="$file//pdox:class[@full = $className]"/>
  <xsl:variable name="target" select="concat('target://', cxr:filename-of-class($class/@full))"/>
  <xsl:variable name="path" select="cxr:string-repeat('../', cxr:substring-count($class/@full, '\'))"/>
  <xsl:variable name="namespace" select="$class/parent::pdox:namespace/@name"/>
  <xsl:variable name="docblock" select="$class/pdox:docblock"/>
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
        <xsl:with-param name="title" select="$class/@full"/>
        <xsl:with-param name="path" select="$path"/>
      </xsl:call-template>
      <body>
        <xsl:call-template name="page-header">
          <xsl:with-param name="title" select="$class/@full"/>
        </xsl:call-template>
        <div class="navigation">
          <xsl:call-template name="navigation">
            <xsl:with-param name="selected" select="$class/@full"/>
            <xsl:with-param name="path" select="$path"/>
          </xsl:call-template>
        </div>
        <div class="pageBody">
          <div class="pageNavigation">
            <xsl:call-template name="file-dynamic-property-links">
              <xsl:with-param
                name="properties"
                select="$docblock/pdox:property|$docblock/pdox:property-read|$docblock/pdox:invalid[@annotation = 'property-read']"/>
              <xsl:with-param name="namespace" select="$namespace"/>
              <xsl:with-param name="path" select="$path"/>
            </xsl:call-template>
            <xsl:call-template name="file-dynamic-method-links">
              <xsl:with-param name="methods" select="$docblock/pdox:method"/>
              <xsl:with-param name="namespace" select="$namespace"/>
              <xsl:with-param name="path" select="$path"/>
            </xsl:call-template>
            <xsl:call-template name="file-property-links">
              <xsl:with-param name="properties" select="$class/pdox:member"/>
              <xsl:with-param name="namespace" select="$namespace"/>
              <xsl:with-param name="path" select="$path"/>
            </xsl:call-template>
            <xsl:call-template name="file-method-links">
              <xsl:with-param name="methods" select="$class/pdox:method|$class/pdox:constructor"/>
              <xsl:with-param name="namespace" select="$namespace"/>
              <xsl:with-param name="path" select="$path"/>
            </xsl:call-template>
            <xsl:text> </xsl:text>
          </div>
          <div class="content">
            <h2 class="className">
              <xsl:call-template name="namespace-ariadne">
                <xsl:with-param name="namespace" select="$namespace"/>
                <xsl:with-param name="path" select="$path"/>
              </xsl:call-template>
              <xsl:if test="string($namespace) != ''">
                <xsl:text>\</xsl:text>
              </xsl:if>
              <xsl:value-of select="@name"/>
            </h2>
            <xsl:call-template name="class-prototype">
              <xsl:with-param name="class" select="$class"/>
              <xsl:with-param name="namespace" select="$class/@namespace"/>
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
              name="superClasses"
              select="cxr:inheritance-superclasses($index, string($class/@full))//pdox:class"/>
            <xsl:if test="count($superClasses) &gt; 1">
              <h3>Inheritance</h3>
              <xsl:call-template name="file-class-inheritance">
                <xsl:with-param
                   name="parents"
                   select="$superClasses"/>
                <xsl:with-param name="path" select="$path"/>
              </xsl:call-template>
            </xsl:if>
            <xsl:variable
               name="children"
              select="cxr:inheritance-children-class($index, string($class/@full))//pdox:class"/>
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
            <xsl:call-template name="file-constants">
              <xsl:with-param name="constants" select="$class/pdox:constant"/>
            </xsl:call-template>
            <xsl:call-template name="file-dynamic-properties">
              <xsl:with-param
                name="properties"
                select="$docblock/pdox:property|$docblock/pdox:property-read|$docblock/pdox:invalid[@annotation = 'property-read']"/>
              <xsl:with-param name="fileName" select="$fileName"/>
              <xsl:with-param name="namespace" select="$namespace"/>
              <xsl:with-param name="path" select="$path"/>
            </xsl:call-template>
            <xsl:call-template name="file-dynamic-methods">
              <xsl:with-param name="methods" select="$docblock/pdox:method"/>
              <xsl:with-param name="fileName" select="$fileName"/>
              <xsl:with-param name="namespace" select="$namespace"/>
              <xsl:with-param name="path" select="$path"/>
            </xsl:call-template>
            <xsl:call-template name="file-properties">
              <xsl:with-param name="properties" select="$class/pdox:member"/>
              <xsl:with-param name="fileName" select="$fileName"/>
              <xsl:with-param name="namespace" select="$namespace"/>
              <xsl:with-param name="path" select="$path"/>
            </xsl:call-template>
            <xsl:call-template name="file-methods">
              <xsl:with-param name="methods" select="$class/pdox:method|$class/pdox:constructor"/>
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

<xsl:template name="file-class-inheritance">
  <xsl:param name="parents"/>
  <xsl:param name="path"/>
  <xsl:param name="offset" select="1"/>
  <xsl:if test="count($parents) &gt; ($offset - 1) and count($parents) &gt; 1">
    <ul>
      <xsl:if test="$offset = 1">
        <xsl:attribute name="class">tree inheritance</xsl:attribute>
      </xsl:if>
      <li>
        <xsl:call-template name="variable-type">
          <xsl:with-param name="type" select="string($parents[position() = $offset]/@full)"/>
          <xsl:with-param name="path" select="string($path)"/>
        </xsl:call-template>
        <xsl:call-template name="file-class-inheritance">
          <xsl:with-param name="parents" select="$parents"/>
          <xsl:with-param name="path" select="$path"/>
          <xsl:with-param name="offset" select="$offset + 1"/>
        </xsl:call-template>
      </li>
    </ul>
  </xsl:if>
</xsl:template>

</xsl:stylesheet>
