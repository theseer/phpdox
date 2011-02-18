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
      <h2 class="className">
         <xsl:value-of select="./@name" />
      </h2>
      <xsl:apply-templates select="file:extends" />
      <xsl:apply-templates select="file:docblock" />
      <div class="classConstants">
         <h3>Constants</h3>
         <ul class="classConstantList">
            <xsl:apply-templates select="file:constant" />
         </ul>
      </div>      
      <div class="classMembers">
         <h3>Members</h3>
         <ul class="classMemberList">
            <xsl:apply-templates select="file:member" />
         </ul>
      </div>
      <div class="classMethods">
         <h3>Methods</h3>
         <ul class="classMethodList">
            <xsl:apply-templates select="file:constructor" />
            <xsl:apply-templates select="file:method" />
         </ul>
      </div>
   </xsl:template>

   <xsl:template match="file:extends">
      <div class="inheritance">
         extending
         <xsl:value-of select="./@class" />
      </div>   
   </xsl:template>
   
   <xsl:template match="file:constant">
      <li class="classConstantItem">
         <xsl:value-of select="./@name" />
         <xsl:text> = </xsl:text>
         <xsl:value-of select="./@value" />
      </li>
   </xsl:template>
   
   <xsl:template match="file:docblock">
      <div class="classDocBlock">
         <xsl:if test="./@author">
            <div class="author">
               <xsl:value-of select="./@author" />
            </div>
         </xsl:if>
         <xsl:if test="./@copyright">
            <div class="copyright">
               <xsl:value-of select="./@copyright"/>
            </div>
         </xsl:if>
         <xsl:if test="file:description">
            <xsl:call-template name="docBlockDescriptons"></xsl:call-template>
         </xsl:if>
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
      <li class="classMemberItem">
         <p class="prefixes">
           <xsl:call-template name="prefixVisibility" />
           <xsl:call-template name="prefixStatic" />
         </p>
         <xsl:value-of select="./@name" />
         <xsl:if test="file:default">
            <xsl:text> = </xsl:text>
            <xsl:value-of select="file:default" />
         </xsl:if>
      </li>
   </xsl:template>

   <xsl:template match="file:method">
      <li class="classMethod">
         <p class="prefixes">
            <xsl:call-template name="prefixVisibility" />
            <xsl:call-template name="prefixFinal" />
            <xsl:call-template name="prefixStatic" />
            <xsl:call-template name="prefixAbstract" />
         </p>
         <p class="classMethodName">
            <xsl:value-of select="./@name"/>
         </p>
         <xsl:apply-templates select="file:docblock" />
         <xsl:if test="file:parameter">
            <xsl:call-template name="methodParameter" />
         </xsl:if>
      </li>
   </xsl:template>

   <xsl:template match="file:parameter">
      <li class="classMethodParameter">
         <xsl:call-template name="typeHint" />
         <xsl:call-template name="prefixByReference" />
         <xsl:value-of select="./@name" />
         <xsl:text> </xsl:text>
         <xsl:call-template name="postfixOptional" />
      </li>
   </xsl:template>



   <xsl:template name="docBlockDescriptons">
      <div class="description">
         <xsl:if test="file:description/@compact">
            <div class="shortDescription">
               <xsl:value-of select="file:description/@compact" />
            </div>
         </xsl:if>
         <xsl:if test="file:description/text()">
            <div class="longDescription">
                <xsl:value-of select="file:description/text()" />
            </div>
         </xsl:if>
      </div>
   </xsl:template>

   <xsl:template name="methodParameter">
      <div class="classMethodParameters">
         <ul class="classMethodParameterList">
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
         <xsl:text> </xsl:text>
      </xsl:if>
   </xsl:template>

   <xsl:template name="postfixOptional">
      <xsl:if test="./@optional='true'">
         <xsl:text>= </xsl:text>
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
         <xsl:text>final </xsl:text>
      </xsl:if>
   </xsl:template>

   <xsl:template name="prefixVisibility">
      <xsl:value-of select="./@visibility" />
      <xsl:text> </xsl:text>
   </xsl:template>

   <xsl:template name="prefixStatic">
      <xsl:if test="./@static='true'">
         <xsl:text>static </xsl:text>
      </xsl:if>
   </xsl:template>

   <xsl:template name="prefixAbstract">
      <xsl:if test="./@abstract='true'">
         <xsl:text>abstract </xsl:text>
      </xsl:if>
   </xsl:template>
</xsl:stylesheet>
