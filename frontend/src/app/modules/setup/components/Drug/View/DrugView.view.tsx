import React, {FC} from 'react'
import EditAction from 'src/app/components/Actions/EditAction'
import DeleteAction from 'src/app/components/Actions/DeleteAction'
import {DrugAction} from '../Actions/Drug.actions'
import ViewTabList from 'src/app/components/Tab/ViewTabList'
import DrugInfoViewTab from '../Tabs/DrugInfoView.tab'
import DrugSubstitutesViewTab from '../Tabs/DrugSubstitutesView.tab'
import DrugStockViewTab from '../Tabs/DrugStockView.tab'
import {useLang} from 'src/app/hooks/useLang'

const DrugView: FC<any> = (props) => {
  const {itemData, loading, handleCallbackFunc} = props
  const {t} = useLang()

  const viewTabListData: any = [
    {
      tabIndex: 1,
      label: t('Drug Info'),
      permission: '',
      component: <DrugInfoViewTab itemData={itemData} />,
    },
    {
      tabIndex: 2,
      label: t('Substitutes'),
      permission: '',
      component: <DrugSubstitutesViewTab itemData={itemData} />,
    },
    {
      tabIndex: 3,
      label: t('Stock'),
      permission: '',
      component: <DrugStockViewTab itemData={itemData} />,
    },
  ]

  return (
    <div className='card card-body position-relative'>
      <div className='row mb-7'>
        <div className='col-lg-12'>
          <EditAction
            entityId={itemData.id}
            actionItem={DrugAction.COMMON_ACTION.EDIT}
            handleCallbackFunc={handleCallbackFunc}
          />
          <DeleteAction
            entityId={itemData.id}
            actionItem={DrugAction.COMMON_ACTION.DELETE}
            handleCallbackFunc={handleCallbackFunc}
          />
        </div>
      </div>

      {loading === false && <ViewTabList activeTabIndex={'1'} viewTabListData={viewTabListData} />}
    </div>
  )
}

export default React.memo(DrugView)
