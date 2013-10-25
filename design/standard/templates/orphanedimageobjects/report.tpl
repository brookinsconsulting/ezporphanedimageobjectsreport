<form name="eZPOrphanedImageObjectsReport" method="post" action={'orphanedimageobjects/report'|ezurl}>

<div class="context-block">

    {* DESIGN: Header START *}<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">

    <h1 class="context-title">{'Orphaned Image Objects Report'|i18n('design/standard/orphanedimageobjects')}</h1>

    {* DESIGN: Mainline *}<div class="header-mainline"></div>

    {* DESIGN: Header END *}</div></div></div></div></div></div>

    {* DESIGN: Content START *}<div class="box-ml"><div class="box-mr"><div class="box-content">

    {* DESIGN: Content END *}</div></div></div>

    <div class="controlbar">
    {* DESIGN: Control bar START *}<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-tc"><div class="box-bl"><div class="box-br">
        <div class="block">
            {if $status}
                <p><span style="color: green">Report Generated!</span> Created: <span style="color: red">{$fileModificationTimestamp}</span></p>{/if}
                {* <input class="button" name="Generate" type="submit" value="{'Regenerate Report'|i18n('design/standard/orphanedimageobjects')}" /> *}
            {if $status}
                <input class="button" name="Download" type="submit" value="{'Download Report'|i18n('design/standard/orphanedimageobjects')}" />
            {/if}
        </div>
    {* DESIGN: Control bar END *}</div></div></div></div></div></div>
    </div>
</div>

</form>