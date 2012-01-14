<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns="http://www.w3.org/1999/xhtml/"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:exsl="http://exslt.org/common"
  xmlns:pdox="http://xml.phpdox.de/src#"
  xmlns:cxr="http://thomas.weinert.info/carica/xr"
  extension-element-prefixes="exsl"
  exclude-result-prefixes="#default pdox cxr">


<!-- callback definitions -->
<xsl:import href="library/library.xsl"/>

<!-- default templates to use and maybe overload -->
<xsl:import href="html.xsl"/>
<xsl:import href="navigation.xsl"/>

<xsl:import href="links.xsl"/>

<xsl:import href="index.xsl"/>
<xsl:import href="classes.xsl"/>
<xsl:import href="interfaces.xsl"/>

<xsl:output
  method="xml"
  encoding="utf-8"
  standalone="yes"
  indent="yes"
  omit-xml-declaration="no"/>
  
  
<xsl:param name="FORCE_USE_PACKAGES" select="false()"/>

<!-- define data variables -->
<xsl:variable name="CLASSES" select="document('source://classes.xml')/pdox:classes"/>
<xsl:variable name="INTERFACES" select="document('source://interfaces.xml')/pdox:interfaces"/>
<xsl:variable name="NAMESPACES" select="document('source://namespaces.xml')/pdox:namespaces/pdox:namespace"/>

<xsl:template match="/">
  <xsl:variable name="consoleOutput" select="cxr:console-write('Generating output from phpDox xml')"/>
  <result>
    <xsl:variable name="index" select="cxr:aggregate($CLASSES, $INTERFACES)"/>
    <xsl:call-template name="dump-structure">
      <xsl:with-param name="index" select="$index"/>
    </xsl:call-template>
    <xsl:call-template name="file-index">
      <xsl:with-param name="index" select="$index"/>
    </xsl:call-template>
    <xsl:call-template name="class-index">
      <xsl:with-param name="index" select="$index"/>
      <xsl:with-param name="classIndex" select="$CLASSES"/>
    </xsl:call-template>
    <xsl:call-template name="classes">
      <xsl:with-param name="index" select="$index"/>
      <xsl:with-param name="classIndex" select="$CLASSES"/>
    </xsl:call-template>
    <xsl:call-template name="interface-index">
      <xsl:with-param name="index" select="$index"/>
      <xsl:with-param name="interfaceIndex" select="$INTERFACES"/>
    </xsl:call-template>
    <xsl:call-template name="interfaces">
      <xsl:with-param name="index" select="$index"/>
      <xsl:with-param name="interfaceIndex" select="$INTERFACES"/>
    </xsl:call-template>
  </result>
</xsl:template>

<xsl:template name="classes">
  <xsl:param name="index" />
  <xsl:param name="classIndex" />
  <xsl:variable name="classCount" select="count($classIndex//pdox:class)" />
  <xsl:variable name="consoleOutputStart" select="cxr:console-write('Generating class files')"/>
  <xsl:for-each select="$classIndex//pdox:class">
    <xsl:variable name="fileName" select="concat('source://', @xml)"/>
    <xsl:variable name="consoleProgress" select="cxr:console-progress(position() = 1, $classCount)"/>
    <xsl:call-template name="file-class">
      <xsl:with-param name="index" select="$index"/>
      <xsl:with-param name="fileName" select="$fileName"/>
      <xsl:with-param name="className" select="@full"/>
    </xsl:call-template>
  </xsl:for-each>
  <xsl:variable name="consoleOutputDone" select="cxr:console-write('&#10;')"/>
</xsl:template>

<xsl:template name="interfaces">
  <xsl:param name="index" />
  <xsl:param name="interfaceIndex" />
  <xsl:variable name="interfaceCount" select="count($interfaceIndex//pdox:interface)" />
  <xsl:variable name="consoleOutputStart" select="cxr:console-write('Generating interface files')"/>
  <xsl:for-each select="$interfaceIndex//pdox:interface">
    <xsl:variable name="fileName" select="concat('source://', @xml)"/>
    <xsl:variable name="consoleProgress" select="cxr:console-progress(position() = 1, $interfaceCount)"/>
    <xsl:call-template name="file-interface">
      <xsl:with-param name="index" select="$index"/>
      <xsl:with-param name="fileName" select="$fileName"/>
      <xsl:with-param name="interfaceName" select="@full"/>
    </xsl:call-template>
  </xsl:for-each>
  <xsl:variable name="consoleOutputDone" select="cxr:console-write('&#10;')"/>
</xsl:template>

<xsl:template name="dump-structure">
  <xsl:param name="index" />
  <exsl:document
    href="target://dump-structure.xml"
    method="xml"
    encoding="utf-8"
    standalone="yes"
    indent="yes">
    <xsl:copy-of select="$index"/>
  </exsl:document>
</xsl:template>

</xsl:stylesheet>