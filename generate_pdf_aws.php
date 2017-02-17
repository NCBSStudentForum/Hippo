<?php

include_once 'database.php';
include_once 'tohtml.php';

function awsToTex( $aws, $with_picture = true )
{
    // First sanities the html before it can be converted to pdf.
    foreach( $aws as $key => $value )
    {
        // See this 
        // http://stackoverflow.com/questions/9870974/replace-nbsp-characters-that-are-hidden-in-text
        $value = htmlentities( $value, null, 'utf-8' );
        $value = str_replace( '&nbsp;', '', $value );
        $value = preg_replace( '/\s+/', ' ', $value );
        $value = html_entity_decode( trim( $value ) );
        $aws[ $key ] = $value;
    }

    $speaker = __ucwords__( loginToText( $aws[ 'speaker' ] , false ));

    $supervisors = array( __ucwords__( 
        loginToText( findAnyoneWithEmail( $aws[ 'supervisor_1' ] ), false ))
                ,  __ucwords__( 
        loginToText( findAnyoneWithEmail( $aws[ 'supervisor_2' ] ), false ))
            );
    $supervisors = array_filter( $supervisors );

    $tcm = array( );
    array_push( $tcm, __ucwords__( 
        loginToText( findAnyoneWithEmail( $aws[ 'tcm_member_1' ] ), false ))
            , __ucwords__( 
        loginToText( findAnyoneWithEmail( $aws[ 'tcm_member_2' ] ), false ))
            ,  __ucwords__( 
        loginToText( findAnyoneWithEmail( $aws[ 'tcm_member_3' ] ), false ))
            , __ucwords__( 
        loginToText( findAnyoneWithEmail( $aws[ 'tcm_member_4' ] ), false ))
        );
    $tcm = array_filter( $tcm );

    $title = __ucwords__( $aws[ 'title' ]);
    $abstract = $aws[ 'abstract' ];

    // Add user image.
    $imagefile = $_SESSION[ 'conf' ]['data']['user_imagedir'] . '/' . 
        $aws['speaker'] . '.png';
    if( ! file_exists( $imagefile ) )
        $imagefile = __DIR__ . '/data/null.png';

    $speakerImg = '\includegraphics[width=5cm]{' . $imagefile . '}';

    // Date and plate
    $dateAndPlace =  humanReadableDate( $aws[ 'date' ] ) .  
            ', 4:00pm at \textbf{Hapus (LH1)}';
    $dateAndPlace = '\faCalendarCheckO \quad ' . $dateAndPlace;

    $head = '\begin{tikzpicture}[ every node/.style={rectangle
        ,inner sep=1pt,node distance=5mm,text width=0.65\textwidth} ]';
    $head .= '\node[text width=5cm] (image) at (0,0) {' . $speakerImg . '};';
    $head .= '\node[above right=of image] (schedule)  {\hfill ' . 
                $dateAndPlace . '};';
    $head .= '\node[right=of image] (title) { ' .  '{\LARGE ' . $title . '} };';
    $head .= '\node[below=of title] (author) { ' .  '{' . $speaker . '} };';
    $head .= '\end{tikzpicture}';


    // Do not generate the preamble.
    $tex = array( "\documentclass[]{article}"
        , "\usepackage[margin=20mm,a4paper]{geometry}"
        , "\usepackage[]{graphicx}"
        , "\usepackage[]{amsmath,amssymb}"
        , "\usepackage{tikz}"
        , "\usepackage{fontawesome}"
        , '\usepackage{fancyhdr}'
        , '\linespread{1.2}'
        , '\pagestyle{fancy}'
        , '\pagenumbering{gobble}'
        , '\lhead{\textsc{Annual Work Seminar} }'
        , '\rhead{National Center for Biological Sciences, Bangalore \\\\ 
            TATA Institute of Fundamental Research, Mumbai}'
        , '\usetikzlibrary{calc,positioning,arrows}'
        //, '\usepackage[T1]{fontenc}'
        , '\usepackage[utf8]{inputenc}'
        , '\usepackage[]{lmodern}'
        , '\begin{document}'
        );

    // Header
    $tex[] = $head;

    $tex[] = '\par';

    // remove html formating before converting to tex.
    file_put_contents( '/tmp/abstract.html', $abstract );
    $cmd = 'python ' . __DIR__ . '/html2other.py';
    $texAbstract = `$cmd /tmp/abstract.html tex`;
    if( strlen(trim($texAbstract)) > 10 )
        $abstract = $texAbstract;

    //$tex[] = $dateAndPlace;

    // Title and abstract
    $tex[] = '{\large ' . $abstract . '}';

    $extra = '\begin{table}[ht!]';
    $extra .= '\begin{tabular}{ll}';
    $extra .= '\textbf{Supervisor(s)} & ' . implode( ",", $supervisors) . '\\\\';
    $extra .= '\textbf{Thesis Committee Member(s)} & ' . implode( ", ", $tcm ) . '\\\\';
    $extra .= '\end{tabular}';
    $extra .= '\end{table}';

    $tex[] = $extra;
    $tex[] = '\end{document}';
    return implode( "\n", $tex );
}

if( ! array_key_exists( 'speaker', $_GET ) )
{
    echo printInfo( 'Invalid request' );
}
else 
{
    $date = $_GET[ 'date' ];
    $speaker = $_GET[ 'speaker' ];
    $aws = getTableEntry( 'annual_work_seminars', 'date,speaker', $_GET );
    // Check in upcoming aws
    if( ! $aws )
        $aws = getTableEntry( 'upcoming_aws', 'date,speaker', $_GET );

    if( ! $aws )
        echo printInfo( "No AWS found for speaker $speaker and date $date" );
    else
    {
        $teX = awsToTex( $aws );
        $texFileName = __DIR__ . "/data/" . $aws['speaker'] . $aws['date'] . ".tex";
        $pdfFileUrl = __DIR__ . '/data/' . $aws[ 'speaker' ] . $aws['date'] . '.pdf';
        $outdir = __DIR__ . "/data";
        file_put_contents( $texFileName, $teX );
        $res = `pdflatex --output-directory $outdir $texFileName`;

        if( file_exists( $pdfFileUrl ) )
        {
            echo printInfo( "Successfully genered pdf document " . 
               basename( $pdfFileUrl ) );
            goToPage( 'download_pdf.php?filename=' .$pdfFileUrl, 0 );
        }
        else
        {
            echo printWarning( "Failed to genered pdf document <br>
                This is usually due to hidden special characters 
                in your abstract. You need to clean your entry up." );
            echo printWarning( "Error message <small>This is only for diagnostic
                purpose. Show it to someone who is good with LaTeX </small>" );
            echo "<pre> $res </pre>";
        }

        unlink( $texFileName );
    }
};

echo "<br/>";
echo closePage( );

?>
