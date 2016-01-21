<?php
namespace Cyan\Library;

class ApplicationApi extends ApplicationBase
{
    /**
     * @since 1.0.0
     */
    public function execute()
    {
        if ($this->Router->countRoutes() == 0) {
            throw new ApplicationException(sprintf('%s Application Router not have any route.', $this->name));
        }

        $this->trigger('BeforeExecute', $this);

        $route_info = $this->Router->dispatchFromRequest();

        return 'teste';
    }
}