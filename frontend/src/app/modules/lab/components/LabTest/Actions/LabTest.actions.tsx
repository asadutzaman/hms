import DeleteAction from 'src/app/components/Actions/DeleteAction'
import EditAction from 'src/app/components/Actions/EditAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import LabTestFormController from '../Form/LabTestForm.controller'
import LabTestViewController from '../View/LabTestView.controller'

export const LabTestAction = {
  COMMON_ACTION: {
    CREATE: {
      type: 'item',
      title: 'New Lab Test',
      action: 'create',
      link: {to: ''},
      permission: 'auth:lab-test:create',
      component: LabTestFormController,
      className: 'grid-view-action',
    },
    EDIT: {
      type: 'item',
      title: 'Edit Lab Test',
      action: 'edit',
      link: {to: ''},
      permission: 'auth:lab-test:edit',
      component: LabTestFormController,
      className: 'grid-view-action',
      modalId: 'edit',
    },
    VIEW: {
      type: 'item',
      title: 'View Lab Test',
      action: 'view',
      link: {to: ''},
      permission: 'auth:lab-test:view',
      component: LabTestViewController,
      className: 'grid-view-action',
      modalId: 'view',
    },
    DELETE: {
      type: 'item',
      title: 'Delete Lab Test',
      action: 'delete',
      link: {to: ''},
      permission: 'auth:lab-test:delete',
      component: '',
      className: 'grid-view-action',
    },
  },
  LIST_ITEM_ACTION: [
    {
      type: 'item',
      title: 'View',
      action: 'active',
      link: {to: ''},
      permission: 'auth:lab-test:view',
      component: ViewAction,
      className: '',
      modalId: 'view',
      icon: 'information-4',
    },
    {
      type: 'item',
      title: 'Edit',
      action: 'inactive',
      link: {to: ''},
      permission: 'auth:lab-test:edit',
      component: EditAction,
      className: '',
      modalId: 'edit',
      icon: 'pencil',
    },
    {
      type: 'item',
      title: 'Delete',
      action: 'delete',
      link: {to: ''},
      permission: 'auth:lab-test:delete',
      component: DeleteAction,
      className: '',
      modalId: 'delete',
      icon: 'trash',
    },
  ],
  BULK_ACTION: {
    permission: 'auth:lab-test:multiSelect',
    action_list: [
      {
        type: 'item',
        title: 'Delete',
        action: 'delete',
        link: {to: ''},
        permission: 'auth:lab-test:delete',
        component: '',
        className: 'grid-view-action',
      },
    ],
  },
}
