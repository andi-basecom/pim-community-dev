import React, {useRef} from 'react';
import {useTranslate} from '@akeneo-pim-community/shared';
import {useScrollIntoView} from '../hooks/useScrollIntoView';
import {CloseIcon, IconButton, RowIcon, Table} from 'akeneo-design-system';
import styled from 'styled-components';

interface newOptionPlaceholderProps {
  cancelNewOption: () => void;
  isDraggable: boolean;
}

const NewOptionPlaceholder = ({cancelNewOption, isDraggable}: newOptionPlaceholderProps) => {
  const translate = useTranslate();
  const placeholderRef = useRef<HTMLDivElement>(null);

  useScrollIntoView(placeholderRef);

  return (
    <TableRow isSelected={true}>
      {!isDraggable && (
        <TableCellNoDraggable>
          <HandleContainer>
            <RowIcon size={16} />
          </HandleContainer>
        </TableCellNoDraggable>
      )}
      <TableCellLabel rowTitle={true}>&nbsp;</TableCellLabel>
      <Table.Cell>
        {translate('pim_enrich.entity.attribute_option.module.edit.new_option_code')}
      </Table.Cell>
      <Table.Cell>&nbsp;</Table.Cell>
      <TableActionCell>
        <IconButton
          icon={<CloseIcon />}
          onClick={() => cancelNewOption()}
          title={translate('pim_common.delete')}
          ghost="borderless"
          level="tertiary"
        />
      </TableActionCell>
    </TableRow>
  );
};

const TableRow = styled(Table.Row)`
  td:first-child {
    color: #f0f1f3;
  }
`;

const TableCellLabel = styled(Table.Cell)`
  width: 35%;
`;

const TableCellNoDraggable = styled(Table.Cell)`
  width: 40px;
`;

const HandleContainer = styled.div`
  cursor: grab;
  display: flex;
  align-items: center;
  justify-content: center;
`;

const TableActionCell = styled(Table.ActionCell)`
  width: 50px;
`;

export default NewOptionPlaceholder;
