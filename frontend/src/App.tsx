import { Route, Switch } from "wouter";
import Layout from "./components/Layout";
import Home from "./pages/Home";
import Apply from "./pages/Apply";
import AppView from "./pages/AppView";
import Apps from "./pages/Apps";
import Characters from "./pages/Characters";
import Character from "./pages/Character";
import Raids from "./pages/Raids";
import Raid from "./pages/Raid";
import BattleNet from "./pages/BattleNet";
import Log from "./pages/Log";
import Debug from "./pages/Debug";
import { Typography } from "@mui/material";

export default function App() {
    return (
        <Layout>
            <Switch>
                <Route path="/" component={Home} />
                <Route path="/apply" component={Apply} />
                <Route path="/app/:id" component={AppView} />
                <Route path="/apps" component={Apps} />
                <Route path="/characters" component={Characters} />
                <Route path="/character/:id" component={Character} />
                <Route path="/raids" component={Raids} />
                <Route path="/raid/:id" component={Raid} />
                <Route path="/bnet" component={BattleNet} />
                <Route path="/log" component={Log} />
                <Route path="/debug" component={Debug} />
                <Route>
                    <Typography
                        variant="h5"
                        sx={{ textAlign: "center", py: 8 }}
                    >
                        404 — Page Not Found
                    </Typography>
                </Route>
            </Switch>
        </Layout>
    );
}
