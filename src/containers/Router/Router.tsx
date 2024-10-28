import { Suspense } from "react";
import { BrowserRouter, Route, Routes } from "react-router-dom";
import Home from "../Home";
import Displays from "../Displays";
import Campaigns from "../Campaigns";
import Media from "../Media";
import Fallback from "./Fallback";
import AdminLayout from "../AdminLayout";

const Router = () => (
  <BrowserRouter>
    <Suspense fallback="Loading Template">
      <Suspense fallback={<Fallback />}>
        <Routes>
          <Route path="/admin" element={<AdminLayout />}>
            <Route path="displays" element={<Displays />} />
            <Route path="campaigns" element={<Campaigns />} />
            <Route path="media" element={<Media />} />
            <Route index path="*" element={<Displays />} />
          </Route>
          <Route path="*" element={<Home />} />
        </Routes>
      </Suspense>
    </Suspense>
  </BrowserRouter>
);

export default Router;