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

<xsl:template name="file-index">
  <xsl:param name="index" />
  <xsl:variable name="consoleOutput" select="cxr:console-write('Generating documentation index')"/>
  <exsl:document
    href="target://index{$OUTPUT_EXTENSION}"
    method="xml"
    encoding="utf-8"
    standalone="yes"
    doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"
    doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"
    indent="yes"
    omit-xml-declaration="yes">
    <html>
      <xsl:call-template name="html-head">
        <xsl:with-param name="title">Index</xsl:with-param>
      </xsl:call-template>
      <body>
        <xsl:call-template name="page-header"/>
        <div class="navigation">
          <xsl:call-template name="navigation"/>
        </div>
        <div class="content">
          <xsl:text> </xsl:text>
        </div>
        <xsl:call-template name="page-footer"/>
      </body>
    </html>
  </exsl:document>
</xsl:template>

</xsl:stylesheet>
