<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output method="text" encoding="UTF-8" indent="no"/>
<xsl:param name="direction"/>
<xsl:param name="description"/>
<xsl:param name="showTitle"/>
<xsl:param name="showDescription"/>
<xsl:param name="footer"/>
<xsl:template match="/project">@startuml
<xsl:if test="$showTitle">
<xsl:variable name="name" select="@name"/>
title
<xsl:value-of select="$name"/>
end title
</xsl:if>
<xsl:if test="$showDescription">
caption
<xsl:value-of select="$description"/>
end caption
</xsl:if>
<xsl:if test="$footer">
right footer
<xsl:value-of select="$footer"/>
end footer
</xsl:if>
<xsl:choose>
<xsl:when test="'horizontal' = $direction">
left to right direction
</xsl:when>
<xsl:otherwise>
top to bottom direction
</xsl:otherwise>
</xsl:choose>
skinparam Shadowing false
skinparam ArrowFontColor Black
skinparam ArrowThickness 2
skinparam UseCaseBackgroundColor #FFFECC
skinparam UseCaseBorderColor #333333
skinparam UseCaseBorderThickness 2
skinparam UseCaseFontColor Black
</xsl:template>
</xsl:stylesheet>
