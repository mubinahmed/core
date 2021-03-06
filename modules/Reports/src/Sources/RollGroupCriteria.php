<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

namespace Gibbon\Module\Reports\Sources;

use Gibbon\Module\Reports\DataSource;

class RollGroupCriteria extends DataSource
{
    public function getSchema()
    {
        return [
            'perGroup' => [
                0 => [
                    'scopeName'           => 'Roll Group',
                    'criteriaName'        => 'Roll Group Comment',
                    'criteriaDescription' => ['sentence'],
                    'value'               => ['randomDigit'],
                    'comment'             => ['paragraph', 6],
                    'valueType'           => 'Comment',
                ],
            ],
            'perStudent' => [
                0 => [
                    'scopeName'           => 'Roll Group',
                    'criteriaName'        => 'Student Comment',
                    'criteriaDescription' => ['sentence'],
                    'value'               => ['randomDigit'],
                    'comment'             => ['paragraph', 6],
                    'valueType'           => 'Comment',
                ],
                1 => [
                    'scopeName'           => 'Roll Group',
                    'criteriaName'        => 'Effort',
                    'criteriaDescription' => ['sentence'],
                    'value'               => ['randomElement', ['Excellent', 'Very Good', 'Good', 'Satisfactory', 'Needs Improvement']],
                    'comment'             => '',
                    'valueType'           => 'Grade Scale',
                ],
            ],
        ];
    }

    public function getData($ids = [])
    {
        $data = ['gibbonStudentEnrolmentID' => $ids['gibbonStudentEnrolmentID'], 'gibbonReportingCycleID' => $ids['gibbonReportingCycleID']];
        $sql = "SELECT (CASE WHEN gibbonReportingCriteria.target = 'Per Group' THEN 'perGroup' ELSE 'perStudent' END) AS groupBy, 
                    gibbonReportingScope.name as scopeName,
                    gibbonReportingCriteria.name as criteriaName,
                    gibbonReportingCriteria.description as criteriaDescription, 
                    gibbonReportingValue.value, 
                    gibbonReportingValue.comment, 
                    gibbonScaleGrade.descriptor,
                    gibbonReportingCriteriaType.valueType, 
                    gibbonRollGroup.name as rollGroupName, 
                    gibbonRollGroup.nameShort as rollGroupNameShort
                FROM gibbonStudentEnrolment 
                JOIN gibbonReportingCriteria ON (gibbonReportingCriteria.gibbonRollGroupID=gibbonStudentEnrolment.gibbonRollGroupID)
                JOIN gibbonReportingValue ON (gibbonReportingCriteria.gibbonReportingCriteriaID=gibbonReportingValue.gibbonReportingCriteriaID AND (gibbonReportingValue.gibbonPersonIDStudent=gibbonStudentEnrolment.gibbonPersonID OR gibbonReportingValue.gibbonPersonIDStudent=0))
                JOIN gibbonReportingCriteriaType ON (gibbonReportingCriteriaType.gibbonReportingCriteriaTypeID=gibbonReportingCriteria.gibbonReportingCriteriaTypeID)
                JOIN gibbonReportingScope ON (gibbonReportingScope.gibbonReportingScopeID=gibbonReportingCriteria.gibbonReportingScopeID)
                JOIN gibbonRollGroup ON (gibbonRollGroup.gibbonRollGroupID=gibbonReportingCriteria.gibbonRollGroupID)
                LEFT JOIN gibbonReportingProgress ON (gibbonReportingProgress.gibbonReportingScopeID=gibbonReportingScope.gibbonReportingScopeID AND gibbonReportingProgress.gibbonRollGroupID=gibbonRollGroup.gibbonRollGroupID AND gibbonReportingProgress.gibbonPersonIDStudent=gibbonStudentEnrolment.gibbonPersonID)
                LEFT JOIN gibbonScaleGrade ON (gibbonScaleGrade.gibbonScaleID=gibbonReportingCriteriaType.gibbonScaleID AND gibbonScaleGrade.gibbonScaleGradeID=gibbonReportingValue.gibbonScaleGradeID)
                WHERE gibbonStudentEnrolment.gibbonStudentEnrolmentID=:gibbonStudentEnrolmentID
                AND gibbonReportingCriteria.gibbonReportingCycleID=:gibbonReportingCycleID
                AND gibbonReportingScope.scopeType='Roll Group'
                AND ((gibbonReportingProgress.status='Complete' AND gibbonReportingCriteria.target = 'Per Student') 
                    OR gibbonReportingCriteria.target = 'Per Group') 
                ORDER BY gibbonReportingScope.sequenceNumber, gibbonReportingCriteria.sequenceNumber, gibbonRollGroup.nameShort";

        return $this->db()->select($sql, $data)->fetchGrouped();
    }
}
