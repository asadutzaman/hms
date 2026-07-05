import { useState, useEffect } from "react";
import { EmployeeApi } from "../../api";

export const useEmployeeList = () => {
    const [employeeList, setEmployeeList] = useState<any>([]);
    const [loadingEmployeeList, setLoadingEmployeeList] = useState<boolean>(false);

    useEffect(() => {
        loadEmployeeList();
    }, []);

    const loadEmployeeList = (): Promise<any> => {
        return new Promise((resolve, reject) => {
            setLoadingEmployeeList(true);
            const payload = {
                $select: "id,name_en,designation_id,status",
                $orderby: "name_en asc",
            };
            EmployeeApi.dropdown(payload)
                .then((res) => {
                    const list = res.data.results;
                    if (list.length > 0) {
                        setEmployeeList(list);
                    }
                    setLoadingEmployeeList(false);
                    resolve(res.data);
                })
                .catch((err) => {
                    setLoadingEmployeeList(false);
                    reject(err);
                });
        });
    };

    return {
        loadingEmployeeList,
        employeeList,
    };
};
