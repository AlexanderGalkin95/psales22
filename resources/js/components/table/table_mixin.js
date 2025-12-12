import TableSettings from "./components/TableSettings";
import TableHeader from "./components/TableHeader";
import TablePagination from "./components/TablePagination";
import TableSelect from "./components/TableSelect";
import TableCheckbox from "./components/TableCheckbox";
import TableAutocomplete from "./components/TableAutocomplete";
import TableButton from "./components/TableButton";
import TableButtons from "./components/TableButtons";
import TableBadge from "./components/TableBadge";
import TableBadges from "./components/TableBadges";
import TableHref from "./components/TableHref";
import TableLink from "./components/TableLink";
import TableCallback from "./components/TableCallback";
import TableDatepicker from "./components/TableDatepicker";
import TableSwitch from "./components/TableSwitch.vue";

export const TableMixin = {
    components : {
        TableHref,
        TableLink,
        TableBadges,
        TableBadge,
        TableButtons,
        TableButton,
        TableAutocomplete,
        TableCheckbox,
        TableSelect,
        TablePagination,
        TableHeader,
        TableSettings,
        TableCallback,
        TableDatepicker,
        TableSwitch
    }
}