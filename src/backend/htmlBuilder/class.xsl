<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
   xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
   xmlns="http://www.w3.org/1999/xhtml"
   xmlns:file="http://phpdox.de/xml#"
   exclude-result-prefixes="#default file"
   >

   <xsl:output method="xml" indent="yes" encoding="utf-8" />

   <xsl:template match="/">
      <html>
         <head>
            <link type="text/css" href="" rel="stylesheet" />
         </head>
         <body>
            <xsl:apply-templates select="file:file/file:namespace" />
            <xsl:apply-templates select="file:file/file:namespace/file:class" />
         </body>
      </html>
   </xsl:template>

   <xsl:template match="file:namespace">
      <div class="classNamespace">
         <p class="classNamespacename"><xsl:value-of select="./@name"/></p>
      </div>
   </xsl:template>

   <xsl:template match="file:class">
      <h2 class="classname">
         <xsl:value-of select="./@name" />
      </h2>
      <xsl:apply-templates select="file:docblock" />
      <div class="classmembers">
         <h3>Members</h3>
         <ul>
            <xsl:apply-templates select="file:member" />
         </ul>
      </div>
      <div class="classmethods">
         <h3>Methods</h3>
         <ul>
            <xsl:apply-templates select="file:constructor" />
            <xsl:apply-templates select="file:method" />
         </ul>
      </div>
   </xsl:template>

   <xsl:template match="file:docblock">
      <xsl:if test="./@autor">
         <div class="author">
            <xsl:value-of select="./@author" />
         </div>
      </xsl:if>
      <xsl:if test="./@copyright">
         <div class="copyright">
            <xsl:value-of select="./@copyright"/>
         </div>
      </xsl:if>
      <div class="description">
      <xsl:choose>
         <xsl:when test="file:description/text()">
            <xsl:value-of select="file:description/text()" />
         </xsl:when>
         <xsl:otherwise>
            <xsl:value-of select="file:description/@compact" />
         </xsl:otherwise>
      </xsl:choose>
      </div>
   </xsl:template>

   <xsl:template match="file:constructor">
      <li class="constructor">
         <p class="prefixes">
            <xsl:call-template name="prefixAbstract" />
            <xsl:call-template name="prefixVisibility" />
            <xsl:call-template name="prefixStatic" />
         </p>
         <xsl:text>Constructor</xsl:text>
         <xsl:call-template name="methodParameter" />
      </li>
   </xsl:template>

   <xsl:template match="file:member">
      <li class="classmember">
         <p class="prefixes">
           <xsl:call-template name="prefixVisibility" />
           <xsl:call-template name="prefixStatic" />
         </p>
         <xsl:value-of select="./@name" />
      </li>
   </xsl:template>

   <xsl:template match="file:method">
      <li class="classmethod">
         <p class="prefixes">
            <xsl:call-template name="prefixVisibility" />
            <xsl:call-template name="prefixFinal" />
            <xsl:call-template name="prefixStatic" />
            <xsl:call-template name="prefixAbstract" />
         </p>
         <p class="methodname">
            <xsl:value-of select="./@name"/>
         </p>
         <xsl:call-template name="methodParameter" />
      </li>
   </xsl:template>

   <xsl:template match="file:parameter">
      <li>
         <xsl:call-template name="typeHint" />
         <xsl:call-template name="prefixByReference" />
         <xsl:value-of select="./@name" />
         <xsl:call-template name="postfixOptional" />
      </li>
   </xsl:template>

<!-- 
   To be moved to a separate file
-->
   <xsl:template name="methodParameter">
      <div class="methodParameters">
            <ul>
              <xsl:apply-templates select="file:docblock" />
              <xsl:apply-templates match="file:parameter" />
            </ul>
         </div>
   </xsl:template>

   <xsl:template name="typeHint">
      <xsl:if test="not(./@type='{unknown}')" >
<!--     
    @type = 'array'
    @type = 'object' => @class

-->
         <xsl:value-of select="./@class" />
      </xsl:if>
   </xsl:template>

   <xsl:template name="postfixOptional">
      <xsl:if test="./@optional='true'">
         <xsl:text> = </xsl:text>
         <xsl:value-of select="file:default/text()" />
      </xsl:if>
   </xsl:template>

   <xsl:template name="prefixByReference" >
      <xsl:if test="./@byreference='true'" >
         <xsl:text>&amp;</xsl:text>
      </xsl:if>
   </xsl:template>

   <xsl:template name="prefixFinal">
      <xsl:if test="./@final='true'">
         final
         <xsl:text> </xsl:text>
      </xsl:if>
   </xsl:template>

   <xsl:template name="prefixVisibility">
      <xsl:value-of select="./@visibility" />
      <xsl:text> </xsl:text>
   </xsl:template>

   <xsl:template name="prefixStatic">
      <xsl:if test="./@static='true'">
         static
         <xsl:text> </xsl:text>
      </xsl:if>
   </xsl:template>

   <xsl:template name="prefixAbstract">
      <xsl:if test="./@abstract='true'">
         abstract
         <xsl:text> </xsl:text>
      </xsl:if>
   </xsl:template>

</xsl:stylesheet>
