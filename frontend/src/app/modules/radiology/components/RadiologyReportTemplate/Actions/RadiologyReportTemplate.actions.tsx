import DeleteAction from 'src/app/components/Actions/DeleteAction'
import EditAction from 'src/app/components/Actions/EditAction'
import ViewAction from 'src/app/components/Actions/ViewAction'
import RadiologyReportTemplateFormController from '../Form/RadiologyReportTemplateForm.controller'
import RadiologyReportTemplateViewController from '../View/RadiologyReportTemplateView.controller'

export const RadiologyReportTemplateAction = {
  COMMON_ACTION: {
    CREATE: {
      type: 'item',
      title: 'New Report Template',
      action: 'create',
      link: {to: ''},
      permission: 'auth:radiology-report-template:create',
      component: RadiologyReportTemplateFormController,
      className: 'grid-view-action',
    },
    EDIT: {
      type: 'item',
      title: 'Edit Report Template',
      action: 'edit',
      link: {to: ''},
      permission: 'auth:radiology-report-template:edit',
      component: RadiologyReportTemplateFormController,
      className: 'grid-view-action',
      modalId: 'edit',
    },
    VIEW: {
      type: 'item',
      title: 'View Report Template',
      action: 'view',
      link: {to: ''},
      permission: 'auth:radiology-report-template:view',
      component: RadiologyReportTemplateViewController,
      className: 'grid-view-action',
      modalId: 'view',
    },
    DELETE: {
      type: 'item',
      title: 'Delete Report Template',
      action: 'delete',
      link: {to: ''},
      permission: 'auth:radiology-report-template:delete',
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
      permission: 'auth:radiology-report-template:view',
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
      permission: 'auth:radiology-report-template:edit',
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
      permission: 'auth:radiology-report-template:delete',
      component: DeleteAction,
      className: '',
      modalId: 'delete',
      icon: 'trash',
    },
  ],
  BULK_ACTION: {
    permission: 'auth:radiology-report-template:multiSelect',
    action_list: [
      {
        type: 'item',
        title: 'Delete',
        action: 'delete',
        link: {to: ''},
        permission: 'auth:radiology-report-template:delete',
        component: '',
        className: 'grid-view-action',
      },
    ],
  },
}
