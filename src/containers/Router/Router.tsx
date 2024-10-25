import { Suspense } from "react";
import { BrowserRouter, Route, Routes } from "react-router-dom";
import Home from "../Home";
import Displays from "../Displays";
import Campaigns from "../Campaigns";
import Media from "../Media";
import Preview from "../Preview";
import Template from "./Template";
import Fallback from "./Fallback";

const Router = () => (
  <BrowserRouter>
    <Suspense fallback="Loading Template">
      <Template>
        <Suspense fallback={<Fallback />}>
          <Routes>
            <Route path="/displays" element={<Displays />} />
            <Route path="/campaigns" element={<Campaigns />} />
            <Route path="/media" element={<Media />} />
            <Route path="/preview" element={<Preview />} />
            <Route path="*" element={<Home />} />
          </Routes>
        </Suspense>
      </Template>
    </Suspense>
  </BrowserRouter>
);

export default Router;