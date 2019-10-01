<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/**
 * Report main page
 *
 * @package    report
 * @copyright  2019 Paulo Jr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('reporteduardoatv4', '', null, '', array('pagelayout' => 'report'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname',  'report_eduardoatv4'));


/**
 * Acessando o banco de dados para buscar todos os courses
 * Salva em uma varíavel (array)
 */

$courses = $DB->get_records('course');

/**
 * Instancia-se a tabela
 * configura o tamanho das colunas e os headers
 */
$table = new html_table();
$table->size = array('85%', '15%');
$table->head = array(
    get_string('col_coursename', 'report_eduardoatv4'),
    get_string('col_status', 'report_eduardoatv4')
);

/**
 * Para cada course, define o nome e o status na tabela.data
 * 
 * Também cria um contador para os courses invisíveis
 */
$invisibleCount = 0;
foreach ($courses as $course) {
    /**
     * concatena os dados no final da tabela
     */
    $table->data[] = array(
        $course->fullname,
        $course->visible  == 1 ?
            get_string('chart_visible', 'report_eduardoatv4') : get_string('chart_invisible', 'report_eduardoatv4')
    );

    /**
     * Contador de couses invisiveis
     */
    if ($course->visible != 1)
        $invisibleCount += 1;
}
/**
 * Renderiza a tabela na tela
 */
echo html_writer::table($table);

/**
 * Popula o gráfico com os dados de quantidade de courses visíveis e invisíveis
 */

$totalCourses = count($courses);
$visible = ((($totalCourses - $invisibleCount) / $totalCourses)) * 100;
$visible = round($visible);
$invisibleCount = 100 - $visible;
$chart_values = array(
    $visible,
    $invisibleCount
);

if (class_exists('core\chart_bar')) {
    /**
     * Criar o gráfico de barras empilhado
     */
    $stackedBar = new core\chart_bar();
    $stackedBar->set_title(get_string('chart_stacked', 'report_eduardoatv4'));
    $stackedBar->set_stacked(true);
    $serieVisible = new core\chart_series(
        get_string('chart_visible', 'report_eduardoatv4'),
        [$visible]
    );
    $serieInvisible = new core\chart_series(
        get_string('chart_invisible', 'report_eduardoatv4'),
        [$invisibleCount]
    );

    /**
     * Labels para o stacked bar
     */
    $stacked_labels = array(
        get_string('col_status', 'report_eduardoatv4')
    );
    $stackedBar->add_series($serieVisible);
    $stackedBar->add_series($serieInvisible);
    $stackedBar->set_labels($stacked_labels);

    /**
     * Renderiza o stackedBar
     */
    echo $OUTPUT->render_chart($stackedBar);

    /**
     * Criando o gráfico de barras comum
     */
    $barChart = new core\chart_bar();
    $barChart->set_title(get_string('chart_bar', 'report_eduardoatv4'));
    $series = new core\chart_series(
        get_string('col_status', 'report_eduardoatv4'),
        $chart_values
    );

    /**
     * Labels para o bar comum
     */
    $bar_labels = array(
        get_string('chart_visible', 'report_eduardoatv4'),
        get_string('chart_invisible', 'report_eduardoatv4')
    );
    $barChart->add_series($series);
    $barChart->set_labels($bar_labels);

    /**
     * Renderiza o bar
     */
    echo $OUTPUT->render_chart($barChart);
}



echo $OUTPUT->footer();
